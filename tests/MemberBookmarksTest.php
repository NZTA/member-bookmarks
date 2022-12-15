<?php

namespace NZTA\MemberBookmark\Tests;

use NZTA\MemberBookmark\Extensions\BookmarksMemberExtension;
use NZTA\MemberBookmark\Extensions\BookmarksPageControllerExtension;
use NZTA\MemberBookmark\Models\BookmarkLink;
use Page;
use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Member;

class MemberBookmarksTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = './MemberBookmarksTest.yml';

    /**
     * @var array
     */
    protected $requiredExtensions = [
        Member::class          => [
            BookmarksMemberExtension::class
        ],
        PageController::class => [
            BookmarksPageControllerExtension::class
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->logOut();
    }

    public function testGetGlobalBookmarks()
    {
        $bookmarks = Injector::inst()->get(BookmarksPageControllerExtension::class)->getGlobalBookmarks();

        // Check global bookmarks count before logged in
        $this->assertEquals(2, $bookmarks->count());
    }

    public function testGlobalBookmarkGroupExclusions()
    {
        // Logged as Member one - (Group: group one)
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);

        $bookmarks = Injector::inst()->get(BookmarksPageControllerExtension::class)->getGlobalBookmarks();

        // Get global bookmarks for Member1 (Exclude Group 'Group one')
        $this->assertEquals(1, $bookmarks->count());

        // Get the first global bookmark for some assertions
        $bookmarkFirst = $bookmarks->first();

        // Ensure we getting the correct bookmark details
        $this->assertEquals('Bookmark two', $bookmarkFirst->Title);
        $this->assertEquals('Page two', $bookmarkFirst->SiteTree()->Title);
    }

    public function testNonMembersDoNotHaveBookmarks()
    {
        $memberObject = new Member();
        $bookmarks = $memberObject->getMemberBookmarks();

        // Check user bookmarks count before logged in
        $this->assertEquals(0, $bookmarks->count());
    }

    public function testGetMemberBookmarks()
    {
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);

        $bookmarks = $member->getMemberBookmarks();

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

    public function testNonMembersCannotBookmark()
    {
        // Ensure Sitetree page been added to bookmark list
        $page = $this->objFromFixture('Page', 'Page1');
        $postData = [
            'ID' => $page->ID
        ];

        $controller = new PageController();
        $request = new HTTPRequest('POST', 'addremovebookmark', '', $postData);
        $request->addHeader('X-Requested-With', 'XMLHttpRequest');

        $response = $controller->addremovebookmark($request);

        // Ensure user cant add bookmarks until logedd in
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAddBookmarkToSiteTree()
    {
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);

        // Ensure Sitetree page been added to bookmark list
        $page = $this->objFromFixture('Page', 'Page1');
        $postData = [
            'ID' => $page->ID
        ];

        $controller = new PageController();
        $request = new HTTPRequest('POST', 'addremovebookmark', '', $postData);
        $request->addHeader('X-Requested-With', 'XMLHttpRequest');

        $response = $controller->addremovebookmark($request);

        // Ensure return status 200 for logged user
        $this->assertEquals(200, $response->getStatusCode());

        // Ensure bookmark saved to database with under this $member
        $bookmark = BookmarkLink::get()->filter([
            'BookmarkMemberID' => $member->ID,
            'SiteTreeID'       => $page->ID
        ])->first();

        // Asserts bookmark and data
        $this->assertTrue($bookmark instanceof BookmarkLink);
        $this->assertEquals($page->Title, $bookmark->Title);
    }

    public function testWhatHappensIfWeDeleteAParentPage()
    {
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);
        $page = $this->objFromFixture('Page', 'Page1');
        $page->delete();

        $this->assertCount(1, Page::get());

        $bookmarks = $member->getMemberBookmarks();

        $this->assertCount(1, $bookmarks);
    }
}
