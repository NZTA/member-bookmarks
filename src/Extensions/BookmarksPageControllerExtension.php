<?php

namespace NZTA\MemberBookmark\Extensions;

use Exception;
use Monolog\Logger;
use NZTA\MemberBookmark\Models\BookmarkLink;
use NZTA\MemberBookmark\Models\GlobalBookmark;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Security;

class BookmarksPageControllerExtension extends DataExtension
{
    private static $allowed_actions = [
        'addremovebookmark',
    ];

    private static $dependencies = [
        'logger' => '%$Psr\Log\LoggerInterface',
    ];

    public $logger;

    /**
     * Helper to get all the {@link GlobalBookmark}s that have been added to the CMS. Excludes any bookmarks that have
     * an ExcludeGroup that the current user is a part of.
     *
     * @return DataList
     */
    public function getGlobalBookmarks()
    {
        $bookmarks = GlobalBookmark::get();

        $member = Security::getCurrentUser();

        // if there is a logged in user check if his group has been excluded
        if ($member) {
            $memberGroupIds = $member->Groups()->column('ID');

            // check if current user belongs to any groups
            if ($bookmarks->count() && count($memberGroupIds)) {
                foreach ($bookmarks as $bookmark) {
                    $excludedGroups = $bookmark->ExcludeGroups();

                    // check if there any excluded groups for this bookmark
                    if ($excludedGroups->count()) {
                        $excludedGroupIds = $excludedGroups->column('ID');

                        // filter out this bookmark if user is in one of the excluded groups
                        if (count(array_intersect($excludedGroupIds, $memberGroupIds))) {
                            $bookmarks = $bookmarks->exclude('ID', $bookmark->ID);
                        }
                    }
                }
            }
        }

        return $bookmarks;
    }

    /**
     * Helper to check if the current page has been bookmarked by the current user.
     *
     * @return bool
     */
    public function IsBookmarked()
    {
        $pageId = $this->owner->data()->ID;
        $member = Security::getCurrentUser();

        if (!$member) {
            return false;
        }

        $bookmark = $member
            ->Bookmarks()
            ->filter('SiteTreeID', $pageId)
            ->first();

        return ($bookmark && $bookmark->exists());
    }

    /**
     * Adding bookmark link for current user if the bookmark has not already been saved. If bookmark exists, bookmark
     * will be removed.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPResponse
     */
    public function addremovebookmark(HTTPRequest $request): HTTPResponse
    {
        // ensure this is an ajax reqeust
        if (!$request->isAjax()) {
            return $this->owner->httpError(400);
        }

        $ID = (int)$request->postVar('ID');

        try {
            $member = Security::getCurrentUser();

            if (($ID > 0) && $member) {
                // set default type
                $type = 'SiteTree';

                // Allow a custom method to define type of BookmarkLink to save based on $request data
                if (method_exists($this->owner, 'getBookmarkType')) {
                    $customType = $this->owner->getBookmarkType($request);
                    $type = ($customType) ? $customType : $type;
                }

                // get filters based on type
                $filters = $this->getBookmarkFilterOptions($request, $type, $ID);

                if (count($filters) > 0) {
                    $memberID = $member->ID;

                    // add default filters
                    $filters['Type'] = $type;
                    $filters['BookmarkMemberID'] = $memberID;

                    // Check if the bookmark already exists for this user
                    $bookmark = BookmarkLink::get()->filter($filters)->first();

                    // If bookmark exists delete it, Otherwise add a new bookmark
                    if ($bookmark) {
                        $bookmark->delete();
                    } else {
                        $this->createBookmarkFromData($request, $type, $ID, $memberID);
                    }
                } else {
                    throw new Exception('No filters found!');
                }

                return $this->successResponse(['PageID' => $ID]);
            }
        } catch (Exception $e) {
            $this->logger->log(
                Logger::ERROR,
                sprintf(
                    'Error adding bookmark - %s',
                    $e->getMessage()
                )
            );
        }

        $errMsg = "Error adding bookmark - Invalid item ID.";
        $useMsg = "";
        if (!$member) {
            $errMsg = "Error adding bookmark - not logged in.";
            $useMsg = "Sorry, you need to log in to favourite a page.";
        }
        // log to push to raygun if it gets here
        $this->logger->log(
            Logger::INFO,
            $errMsg
        );

        return $this->errorResponse($useMsg);
    }

    /**
     * Send back an error response.
     *
     * @return HTTPResponse
     */
    private function errorResponse($description = "")
    {
        $response = $this->owner->getResponse();
        $response->setStatusCode(403);
        $response->addHeader('Content-Type', 'application/json');
        $response->setStatusDescription($description);

        return $response;
    }

    /**
     * Send back a successful response. Also pass back any custom data in the
     * body if required.
     *
     * @param array $data
     * @return HTTPResponse
     */
    private function successResponse($data = [])
    {
        $response = $this->owner->getResponse();
        $response->setStatusCode(200);
        $response->addHeader('Content-Type', 'application/json');

        // provide hook for custom data to be passed back if needed
        if (method_exists($this->owner, 'updateBookmarkSuccessResponse')) {
            $extraData = $this->owner->updateBookmarkSuccessResponse($data);

            // add the extra data to the body of the response
            $response->setBody(json_encode($extraData));
        }

        return $response;
    }

    /**
     * @param HTTPRequest $request
     * @param string $type
     * @param integer $ID
     *
     * @return array
     */
    private function getBookmarkFilterOptions(HTTPRequest $request, $type, $ID)
    {
        switch ($type) {
            case 'SiteTree':
                $siteTreeExists = $this->getSiteTree($ID);
                if ($siteTreeExists) {
                    return [
                        'SiteTreeID' => $ID,
                    ];
                }

                return [];
                break;
            case 'URL':
                return [
                    'URL' => Convert::raw2sql($request->getHeader('Referer')),
                ];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * @param HTTPRequest $request
     * @param string $type
     * @param integer $ID
     * @param integer $memberID
     *
     * @return void
     */
    private function createBookmarkFromData(HTTPRequest $request, $type, $ID, $memberID)
    {
        $bookmarkLink = new BookmarkLink();

        switch ($type) {
            case 'URL':
                $url = Convert::raw2sql($request->getHeader('Referer'));
                $bookmarkLink->Title = $this->getURLTitle($url);
                $bookmarkLink->URL = $url;
                break;
            case 'SiteTree':
            default:
                $siteTree = $this->getSiteTree($ID);
                if ($siteTree) {
                    $bookmarkLink->Title = $siteTree->Title;
                    $bookmarkLink->SiteTreeID = $ID;
                }
                break;
        }

        $bookmarkLink->BookmarkMemberID = $memberID;
        $bookmarkLink->Type = $type;
        $bookmarkLink->write();
    }

    /**
     * @param integer $ID
     *
     * @return DataObject
     */
    private function getSiteTree($ID)
    {
        return SiteTree::get()->filter('ID', (int)$ID)->first();
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function getURLTitle($url)
    {
        $title = '';
        if (method_exists($this->owner, 'updateURLTitle')) {
            $title = $this->owner->updateURLTitle($url);
        }

        return $title;
    }
}
