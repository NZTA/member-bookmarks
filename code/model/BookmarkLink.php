<?php
namespace NZTA\MemberBookmark\Models;

use Sheadawson\Linkable\Models\Link;
use SilverStripe\Security\Member;

class BookmarkLink extends Link
{

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

        // hide specific fields
        $fields->removeByName('BookmarkMemberID');

        return $fields;
    }
}
