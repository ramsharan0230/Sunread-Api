<?php


namespace Modules\NavigationMenu\Observers;

use Modules\Core\Traits\Audit;
use Modules\NavigationMenu\Entities\NavigationMenuItemValue;

class NavigationMenuItemValueObserver
{
    public function created(NavigationMenuItemValue $navigation_menu_item_value)
    {
        Audit::log($navigation_menu_item_value, __FUNCTION__);
    }

    public function updated(NavigationMenuItemValue $navigation_menu_item_value)
    {
        Audit::log($navigation_menu_item_value, __FUNCTION__);

    }

    public function deleted(NavigationMenuItemValue $navigation_menu_item_value)
    {
        Audit::log($navigation_menu_item_value, __FUNCTION__);
    }

}
