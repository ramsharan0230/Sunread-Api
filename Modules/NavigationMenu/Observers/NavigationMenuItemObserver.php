<?php


namespace Modules\NavigationMenu\Observers;

use Modules\Core\Facades\Audit;
use Modules\NavigationMenu\Entities\NavigationMenuItem;

class NavigationMenuItemObserver
{
    public function created(NavigationMenuItem $navigation_menu_item)
    {
        Audit::log($navigation_menu_item, __FUNCTION__);
    }

    public function updated(NavigationMenuItem $navigation_menu_item)
    {
        Audit::log($navigation_menu_item, __FUNCTION__);
    }

    public function deleted(NavigationMenuItem $navigation_menu_item)
    {
        Audit::log($navigation_menu_item, __FUNCTION__);
    }
}
