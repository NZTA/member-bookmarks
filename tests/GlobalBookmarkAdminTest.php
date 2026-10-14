<?php

namespace NZTA\MemberBookmark\Test;

use NZTA\MemberBookmark\Models\GlobalBookmarkModelAdmin;
use Override;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class GlobalBookmarkAdminTest extends SapphireTest
{
    // Changes test time from under half a second to over 8 seconds...
    // but due to the nature of the method used to test, there is more integration than necessary
    // Although not affected (write/delete), the database is queried by ModelAdmin during set up.
    #[Override]
    protected $usesDatabase = true;

    public function testGlobalBookmarksCanBeManuallySorted()
    {
        $admin = GlobalBookmarkModelAdmin::create();

        $request = $admin->getRequest();
        Injector::inst()->registerService($request, HTTPRequest::class);
        $request->setSession(new Session([]));
        $admin->doInit();

        $form = $admin->getEditForm();
        /** @var GridField $grid */
        $grid = $form->Fields()->first();
        $config = $grid->getConfig();
        $component = $config->getComponentByType(GridFieldSortableRows::class);

        $this->assertNotNull($component);
    }
}
