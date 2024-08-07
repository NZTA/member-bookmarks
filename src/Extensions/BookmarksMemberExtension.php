<?php

namespace NZTA\MemberBookmark\Extensions;

use NZTA\MemberBookmark\Models\BookmarkLink;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;
use SilverStripe\View\ArrayData;

/**
 * Responsible for adding Bookmarks specific links to Members.
 */
class BookmarksMemberExtension extends DataExtension
{
    private static $has_many = [
        'Bookmarks' => BookmarkLink::class,
    ];

    private static $has_one = [
        'Member' => Member::class,
    ];

    /**
     * Get bookmarks and split into their respective top level pages
     *
     * @return ArrayList
     */
    public function getMemberBookmarks()
    {
        $bookmarks = [];

        $member = $this->owner;

        foreach ($member->Bookmarks() as $link) {
            switch ($link->Type) {
                case 'URL':
                    $bookmarks = $this->appendUrlBookmark($link, $bookmarks);
                    break;
                case 'SiteTree':
                default:
                    $bookmarks = $this->appendSiteTreeBookmark($link, $bookmarks);
            }
        }

        return new ArrayList(array_values($bookmarks));
    }

    /**
     * Get Link type = SiteTree bookmarks
     *
     * @param \BookmarkLink $link
     * @param array $bookmarks
     *
     * @return array
     */
    private function appendSiteTreeBookmark($link, $bookmarks)
    {
        $siteTree = SiteTree::get()->filter('ID', $link->SiteTreeID)->first();
        $category = [];

        if ($siteTree) {
            // Check the bookmark link is parent
            if ($siteTree->ParentID == 0) {
                $categoryKey = $this->getParentSiteTreeKey($siteTree);
                $category[$categoryKey]['Title'] = $siteTree->Title;
            } else {
                $category = $this->getTopLevelParent($siteTree);
            }

            if ($category) {
                $key = key($category);

                $bookmarks = $this->addCategoryToBookmarks($key, $bookmarks, $category);

                /**
                 * Add bookmark to the related top level page in the Bookmarks key, e.g.
                 *
                 * $bookmarks['Top level page title-1'] = [
                 *     'Title' => 'Top level page title',
                 *     'Bookmarks' => [
                 *         [
                 *             'Title' => 'Bookmark 1',
                 *             'Link' => ...
                 *         ]
                 *     ]
                 * ]
                 */
                $bookmarks[$key]['Bookmarks']->push(new ArrayData([
                    'Title' => $siteTree->Title,
                    'Link'  => $siteTree->Link(),
                ]));
            }
        }

        return $bookmarks;
    }

    /**
     *  Get Link type = URL bookmarks
     *
     * @param \BookmarkLink $link
     * @param array $bookmarks
     *
     * @return array
     */
    private function appendUrlBookmark($link, $bookmarks)
    {
        $url = parse_url($link->URL);
        if ($url !== false) {
            // Remove first and last '/' characters from url path
            $paths = explode('/', trim($url['path'], '/'));
            if ($paths) {
                // Get the first part of a url as a title/key
                // e.g https://test.org/abc/test-page
                // use abc as a key and as a title for parent/category
                $key = $paths[0];
                $category[$key]['Title'] = $key;

                $bookmarks = $this->addCategoryToBookmarks($key, $bookmarks, $category);

                $bookmarks[$key]['Bookmarks']->push(new ArrayData([
                    'Title' => $link->Title,
                    'Link'  => $link->URL,
                ]));
            }
        }

        return $bookmarks;
    }

    /**
     * @param string $key
     * @param array $bookmarks
     * @param array $category
     *
     * @return array
     */
    private function addCategoryToBookmarks($key, $bookmarks, $category)
    {
        // Add combination of top level Title and parent ID as a key and title as the value
        // e.g. $bookmarks[Top level page title-1] = ['Title' => 'Top level page title'];
        if (!array_key_exists($key, $bookmarks)) {
            $categoryData = $category[$key];

            if ($this->owner->hasMethod('updateBookmarkURLCategoryTitle')) {
                $categoryData = $this->owner->updateBookmarkURLCategoryTitle($category[$key]);
            }
            $bookmarks[$key] = $categoryData;
            $bookmarks[$key]['Bookmarks'] = new ArrayList();
        }

        return $bookmarks;
    }

    /**
     * Get the top level page/parent
     *
     * @param SiteTree $siteTree
     *
     * @return array|bool
     */
    private function getTopLevelParent($siteTree)
    {
        $ancestry = $siteTree->getAncestors();

        if ($ancestry->exists()) {
            $topLevelParent = $ancestry->pop();
            $parentKey = $this->getParentSiteTreeKey($topLevelParent);
            return [$parentKey => ['Title' => $topLevelParent->Title]];
        }

        return [];
    }

    /**
     * Generate the key for parent/category array in bookmarks
     *
     * @param \SiteTree $siteTree
     *
     * @return string
     */
    private function getParentSiteTreeKey($siteTree)
    {
        return $siteTree->Title . '-' . $siteTree->ID;
    }
}
