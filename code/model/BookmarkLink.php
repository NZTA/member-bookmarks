<?php
use Sheadawson\Linkable\Models\Link;

class BookmarkLink extends Link
{

    /**
     * @var array
     */
    private static $has_one = [
        'BookmarkMember' => 'Member'
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
