<?php

namespace NZTA\MemberBookmark\Models;

use Sheadawson\Linkable\Models\Link;
use SilverStripe\Security\Member;
use SilverStripe\Forms\FieldList;

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
