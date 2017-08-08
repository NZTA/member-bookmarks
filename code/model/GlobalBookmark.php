<?php

class GlobalBookmark extends Link
{

    /**
     * @var array
     */
    private static $many_many = [
        'ExcludeGroups' => 'Group'
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $groups = Group::get()->map('ID', 'Title')->toArray();
        $fields->addFieldToTab('Root.Main', ListboxField::create('ExcludeGroups', 'Exclude Groups', $groups, '', '', true));

        return $fields;
    }
}