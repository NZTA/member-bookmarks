<?php

namespace NZTA\MemberBookmark\Models;

use NZTA\MemberBookmark\Models\GlobalBookmark;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldConfig;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

class GlobalBookmarkModelAdmin extends ModelAdmin
{
    private static $url_segment = 'global-bookmarks';

    private static $menu_title = 'Global Bookmarks';

    private static string $menu_icon = 'nzta/member-bookmarks:icon/bookmark.svg';

    /**
     * @var array
     */
    private static $managed_models = [
        GlobalBookmark::class,
    ];

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();
        return $config->addComponent(new GridFieldSortableRows('SortOrder'));
    }
}
