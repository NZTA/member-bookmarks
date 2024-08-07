<?php

namespace NZTA\MemberBookmark\Models;

use Sheadawson\Linkable\Models\Link;
use SilverStripe\Forms\FieldList;
use SilverStripe\Security\Member;

class BookmarkLink extends Link
{
    /**
     * @var string
     */
    private static $table_name = "BookmarkLink";

    /**
     * @var array
     */
    private static $has_one = [
        'BookmarkMember' => Member::class,
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('BookmarkMemberID');

        return $fields;
    }
}
