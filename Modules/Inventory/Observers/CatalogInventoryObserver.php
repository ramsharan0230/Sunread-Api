<?php

namespace Modules\Inventory\Observers;

use Modules\Core\Facades\Audit;
use Modules\Inventory\Entities\CatalogInventory;

class CatalogInventoryObserver
{
    public function created(CatalogInventory $catalogInventory)
    {
        Audit::log($catalogInventory, __FUNCTION__);
    }

    public function updated(CatalogInventory $catalogInventory)
    {
        Audit::log($catalogInventory, __FUNCTION__);
    }

    public function deleted(CatalogInventory $catalogInventory)
    {
        Audit::log($catalogInventory, __FUNCTION__);
    }

}
