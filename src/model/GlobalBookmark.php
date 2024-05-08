<?php

namespace NZTA\MemberBookmark\Models;

use Sheadawson\Linkable\Models\Link;
use SilverStripe\Security\Group;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\FieldList;

class GlobalBookmark extends Link
{
    private static $table_name = "GlobalBookmark";

    private static $db = [
        'SortOrder' => 'Int',
    ];

    private static $many_many = [
        'ExcludeGroups' => Group::class,
    ];

    private static $default_sort = 'SortOrder';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('SortOrder');

        $groups = Group::get()->map('ID', 'Title');
        $excludeGroupsTitle = _t(self::class . '.EXCLUDE_GROUPS', 'Exclude Groups');
        $fields->removeByName('ExcludeGroups');
        $fields->addFieldToTab('Root.Main', ListboxField::create('ExcludeGroups', $excludeGroupsTitle, $groups, ''));

        return $fields;
    }

    protected function onBeforeWrite()
    {
        if (!$this->SortOrder) {
            $this->SortOrder = GlobalBookmark::get()->max('SortOrder') + 1;
        }

        parent::onBeforeWrite();
    }
}
