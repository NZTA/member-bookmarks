<?php

class BookmarksGroupExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $belongs_many_many = [
        'GlobalBookmarks' => 'GlobalBookmark'
    ];

}