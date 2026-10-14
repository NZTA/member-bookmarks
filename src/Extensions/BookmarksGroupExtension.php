<?php

namespace NZTA\MemberBookmark\Extensions;

use NZTA\MemberBookmark\Models\GlobalBookmark;
use SilverStripe\Core\Extension;

class BookmarksGroupExtension extends Extension
{
    private static $belongs_many_many = [
        'GlobalBookmarks' => GlobalBookmark::class,
    ];
}
