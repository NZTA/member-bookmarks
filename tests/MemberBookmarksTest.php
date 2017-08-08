<?php

class MemberBookmarksTest extends FunctionalTest
{

    /**
     * @var string
     */
    protected static $fixture_file = './MemberBookmarksTest.yml';

    /**
     * @var array
     */
    protected $requiredExtensions = [
        'Member'          => [
            'BookmarksMemberExtension'
        ],
        'Page_Controller' => [
            'BookmarksPageControllerExtension'
        ]
    ];

    public function testGetGlobalBookmarks()
    {
        $bookmarks = Injector::inst()->get('BookmarksPageControllerExtension')->getGlobalBookmarks();

        // Check global bookmarks count before logged in
        $this->assertEquals(2, $bookmarks->count());

        // Logged as Member one - (Group: group one)
        $member = $this->objFromFixture('Member', 'Member1');
        $this->logInAs($member);

        $bookmarks = Injector::inst()->get('BookmarksPageControllerExtension')->getGlobalBookmarks();

        // Get global bookmarks for Member1 (Exclude Group 'Group one')
        $this->assertEquals(1, $bookmarks->count());

        // Get the first global bookmark for some assertions
        $bookmarkFirst = $bookmarks->first();

        // Ensure we getting the correct bookmark details
        $this->assertEquals('Bookmark two', $bookmarkFirst->Title);
        $this->assertEquals('Page two', $bookmarkFirst->SiteTree()->Title);

    }

    public function testGetMemberBookmarks()
    {
        $memberObject = new Member();
        $bookmarks = $memberObject->getMemberBookmarks();

        // Check user bookmarks count before logged in
        $this->assertEquals(0, $bookmarks->count());

        $member = $this->objFromFixture('Member', 'Member1');
        $this->logInAs($member);

        $bookmarks = $memberObject->getMemberBookmarks();

        // Ensure we getting correct bookmark category/parent count
        $this->assertEquals(2, $bookmarks->count());

        // Get the first bookmark category/parent for some assertions
        $firstCategory = $bookmarks->first();

        // Ensure the correct Parent Title
        $this->assertEquals('Page one', $firstCategory['Title']);

        // Ensure the correct bookmarks under Parent
        $this->assertTrue($firstCategory['Bookmarks'] instanceof ArrayList);
        $this->assertEquals(2, $firstCategory['Bookmarks']->count());

        // Get the first bookmark under the parent/category for some assertions
        $firstBookmark = $firstCategory['Bookmarks']->first();

        $this->assertEquals('Page two', $firstBookmark->Title);


        // Ensure top level bookmarks getting under same top level category/parent
        $secondCategory = $bookmarks[1];

        // Ensure the category/Parent Title and the bookmark Title same.
        $this->assertEquals('Page four', $secondCategory['Title']);
        $this->assertEquals('Page four', $secondCategory['Bookmarks']->first()->Title);

    }

    public function testAddBookmarkToSiteTree()
    {
        // Ensure Sitetree page been added to bookmark list
        $page = $this->objFromFixture('Page', 'Page1');
        $postData = [
            'ID' => $page->ID
        ];

        $controller = new Page_Controller();
        $request = new SS_HTTPRequest('POST', 'addremovebookmark', '', $postData);
        $request->addHeader('X-Requested-With', 'XMLHttpRequest');

        $response = $controller->addremovebookmark($request);

        // Ensure user cant add bookmarks until logedd in
        $this->assertEquals(403, $response->getStatusCode());

        $member = $this->objFromFixture('Member', 'Member1');
        $this->logInAs($member);

        $response = $controller->addremovebookmark($request);

        // Ensure return status 200 for logged user
        $this->assertEquals(200, $response->getStatusCode());

        // Ensure bookmark saved to database with under this $member
        $bookmark = BookmarkLink::get()->filter(
            [
                'BookmarkMemberID' => $member->ID,
                'SiteTreeID'       => $page->ID
            ]
        )->first();

        // Asserts bookmark and data
        $this->assertTrue($bookmark instanceof BookmarkLink);

        $this->assertEquals($page->Title, $bookmark->Title);

    }

}