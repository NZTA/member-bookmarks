<?php

class GlobalBookmarkModelAdmin extends ModelAdmin
{

    /**
     * @var string
     */
    private static $url_segment = 'global-bookmarks';

    /**
     * @var string
     */
    private static $menu_title = 'Global Bookmarks';

    /**
     * @var array
     */
    private static $managed_models = [
        'GlobalBookmark'
    ];
}
