<?php
namespace NZTA\MemberBookmark\Models;

use gorriecoe\Link\Models\Link;
use SilverStripe\Admin\ModelAdmin;
use NZTA\MemberBookmark\Models\GlobalBookmark;
use SilverStripe\Forms\GridField\GridField;
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

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        if ($this->modelClass == GlobalBookmark::class
            && $gridField = $form->Fields()->dataFieldByName(
                $this->sanitiseClassName($this->modelClass)
            )
        ) {
            if ($gridField instanceof GridField) {
                $gridField->getConfig()
                    ->addComponent(new GridFieldSortableRows('SortOrder'));
            }
        }

        return $form;
    }
}
