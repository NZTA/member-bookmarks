<?php

namespace NZTA\MemberBookmark\Models;

use NZTA\MemberBookmark\Models\GlobalBookmark;
use Sheadawson\Linkable\Models\Link;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows;

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
        GlobalBookmark::class
    ];

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();
        return $config->addComponent(new GridFieldSortableRows('SortOrder'));
    }
}
