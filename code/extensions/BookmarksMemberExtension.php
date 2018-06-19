<?php
use SilverStripe\ORM\DataExtension;

/**
 * This class is responsible for adding Bookmarks specific links to Members.
 * Class BookmarksMemberExtension
 */
class BookmarksMemberExtension extends DataExtension
{

    /**
     * @var array
     */
    private static $has_many = [
        'Bookmarks' => 'BookmarkLink'
    ];

    /**
     * Get bookmarks and split into their respective top level pages
     *
     * @return ArrayList
     */
    public function getMemberBookmarks()
    {
        $bookmarks = [];

        $member = Member::currentUser();

        if ($member) {
            // Get the member bookmark list
            $links = $member->Bookmarks();

            foreach ($links as $link) {
                $linkType = $link->Type;

                switch ($linkType) {
                    case 'URL':
                        $bookmarks = $this->appendUrlBookmark($link, $bookmarks);
                        break;
                    case 'SiteTree':
                    default:
                        $bookmarks = $this->appendSiteTreeBookmark($link, $bookmarks);
                        break;
                }
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

            if (count($category) > 0) {
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
                    'Link'  => $siteTree->Link()
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
            if (count($paths) > 0) {
                // Get the first part of a url as a title/key
                // e.g https://test.org/abc/test-page
                // use abc as a key and as a title for parent/category
                $key = $paths[0];
                $category[$key]['Title'] = $key;

                $bookmarks = $this->addCategoryToBookmarks($key, $bookmarks, $category);

                $bookmarks[$key]['Bookmarks']->push(new ArrayData([
                    'Title' => $link->Title,
                    'Link'  => $link->URL
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
        $siteTreeParent = SiteTree::get()->filter('ID', $siteTree->ParentID)->first();

        if ($siteTreeParent) {
            if ($siteTreeParent->ParentID == 0) {
                $parentKey = $this->getParentSiteTreeKey($siteTreeParent);
                $parent[$parentKey]['Title'] = $siteTreeParent->Title;
                return $parent;
            }

            return $this->getTopLevelParent($siteTreeParent);
        }

        return false;
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
