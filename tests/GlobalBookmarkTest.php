<?php

namespace NZTA\MemberBookmark\Test;

use NZTA\MemberBookmark\Models\GlobalBookmark;
use Override;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\ListboxField;

class GlobalBookmarkTest extends SapphireTest
{
    #[Override]
    protected $usesDatabase = true;

    public function testGroupExclusionFieldIsAddedToCMSFields()
    {
        $bookmark = GlobalBookmark::create();
        $fields = $bookmark->getCMSFields();
        $groupExclusionsField = $fields->dataFieldByName('ExcludeGroups');
        $this->assertInstanceOf(ListboxField::class, $groupExclusionsField);
    }

    public function testSortOrderIsCreatedOnWrite()
    {
        $this->assertEquals(0, GlobalBookmark::get()->max('SortOrder'));

        $controlSubject = GlobalBookmark::create();
        $controlSubject->SortOrder = 20;
        $controlSubject->write();
        $this->assertSame(20, GlobalBookmark::get()->max('SortOrder'));

        $testSubject = GlobalBookmark::create();
        $testSubject->write();
        $this->assertSame(21, $testSubject->SortOrder);
        $this->assertSame(21, GlobalBookmark::get()->max('SortOrder'));
    }
}
