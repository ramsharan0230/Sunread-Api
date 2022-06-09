<?php

namespace Modules\Inventory\Repositories;

use Exception;
use Modules\Core\Repositories\BaseRepository;
use Modules\Inventory\Entities\CatalogInventory;
use Modules\Inventory\Entities\CatalogInventoryItem;

class CatalogInventoryItemRepository extends BaseRepository
{
    public function __construct(CatalogInventoryItem $catalogInventoryItem)
    {
        $this->model = $catalogInventoryItem;
        $this->model_key = "Catalog Inventory Item";
        $this->rules = [];
    }

    public function filterByProduct(int $id): mixed
    {
        try
        {
            $inventories = CatalogInventory::whereProductId($id)->get();

            $inventory_item_ids = $inventories->map( function ($inventory) {
                return $inventory->catalog_inventory_items->map( function ($item) {
                    return $item->id;
                });
            })->flatten(1)->toArray();
            $query = $this->model::query();
            $query = $query->whereIn("id", $inventory_item_ids);
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return $query;
    }
}
