<?php

namespace NZTA\MemberBookmark\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

class BookmarksSiteConfigExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'GlobalBookmarksHeading' => 'Varchar(255)'
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $globalBookmarksHeading = TextField::create(
            'GlobalBookmarksHeading',
            _t(self::class . '.GLOBAL_HEADING', 'Global Bookmarks Heading')
        );
        $globalBookmarksHeading->setDescription(_t(
            self::class . '.GLOBAL_HEADING_DESCRIPTION',
            'This is the heading displayed above the list of Global Bookmarks'
        ));
        $fields->addFieldToTab('Root.Bookmarks', $globalBookmarksHeading);
    }
}
