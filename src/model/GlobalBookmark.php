<?php
namespace NZTA\MemberBookmark\Models;

use gorriecoe\Link\Models\Link;
use SilverStripe\Security\Group;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\FieldList;

class GlobalBookmark extends Link
{
    /**
     * @var string
     */
    private static $table_name = "GlobalBookmark";

    private static $db = [
        'SortOrder' => 'Int',
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'ExcludeGroups' => Group::class,
    ];

    private static $default_sort = 'SortOrder';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('SortOrder');

        $groups = Group::get()->map('ID', 'Title')->toArray();
        $fields->addFieldToTab('Root.Main', ListboxField::create('ExcludeGroups', 'Exclude Groups', $groups, '', '', true));

        return $fields;
    }

    /**
     * Here we check if this GlobalBookmark has a
     * SortOrder value. If not we assign it one.
     */
    public function onBeforeWrite()
    {
        if (!$this->SortOrder) {
            $this->SortOrder = GlobalBookmark::get()->max('SortOrder') + 1;
        }

        parent::onBeforeWrite();
    }
}
