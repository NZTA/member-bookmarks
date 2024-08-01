<?php

namespace NZTA\MemberBookmark\Test;

use NZTA\MemberBookmark\Models\GlobalBookmark;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\ListboxField;

class GlobalBookmarkTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testGroupExclusionFieldIsAddedToCMSFields()
    {
        $bookmark = new GlobalBookmark();
        $fields = $bookmark->getCMSFields();
        $groupExclusionsField = $fields->dataFieldByName('ExcludeGroups');
        $this->assertInstanceOf(ListboxField::class, $groupExclusionsField);
    }

    public function testSortOrderIsCreatedOnWrite()
    {
        $this->assertEquals(0, GlobalBookmark::get()->max('SortOrder'));

        $controlSubject = new GlobalBookmark();
        $controlSubject->SortOrder = 20;
        $controlSubject->write();
        $this->assertSame(20, GlobalBookmark::get()->max('SortOrder'));

        $testSubject = new GlobalBookmark();
        $testSubject->write();
        $this->assertSame(21, $testSubject->SortOrder);
        $this->assertSame(21, GlobalBookmark::get()->max('SortOrder'));
    }
}
