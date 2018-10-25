<?php
namespace NZTA\MemberBookmark\Models;

use Sheadawson\Linkable\Models\Link;
use SilverStripe\Admin\ModelAdmin;
use NZTA\MemberBookmark\Models\GlobalBookmark;

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
}
