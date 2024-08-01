<?php

namespace NZTA\MemberBookmark\Extensions;

use NZTA\MemberBookmark\Models\GlobalBookmark;
use SilverStripe\ORM\DataExtension;

class BookmarksGroupExtension extends DataExtension
{
    private static $belongs_many_many = [
        'GlobalBookmarks' => GlobalBookmark::class,
    ];
}
