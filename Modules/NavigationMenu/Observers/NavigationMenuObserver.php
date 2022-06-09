<?php


namespace Modules\NavigationMenu\Observers;

use Modules\Core\Facades\Audit;
use Modules\NavigationMenu\Entities\NavigationMenu;

class NavigationMenuObserver
{
    public function created(NavigationMenu $navigation_menu)
    {
        Audit::log($navigation_menu, __FUNCTION__);
    }

    public function updated(NavigationMenu $navigation_menu)
    {
        Audit::log($navigation_menu, __FUNCTION__);
    }

    public function deleted(NavigationMenu $navigation_menu)
    {
        Audit::log($navigation_menu, __FUNCTION__);
    }
}
