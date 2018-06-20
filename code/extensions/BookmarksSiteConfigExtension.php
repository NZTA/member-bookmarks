<?php
namespace NZTA\MemberBookmark\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;

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
        $fields->addFieldToTab(
            'Root.Bookmarks',
            TextField::create('GlobalBookmarksHeading', 'Global Bookmarks Heading')
                ->setDescription('This is the heading displayed above the list of Global Bookmarks')
        );
    }

}
