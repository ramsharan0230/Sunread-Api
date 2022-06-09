<?php

namespace Modules\Inventory\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Inventory\Entities\CatalogInventory;
use Modules\Inventory\Entities\CatalogInventoryItem;
use Modules\Inventory\Exceptions\InventoryCannotBeLessThanZero;

class LogCatalogInventoryItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        DB::beginTransaction();

        try
        {
            $data = $this->validateData($this->data);
            $catalog_inventory = CatalogInventory::whereProductId($data["product_id"])->whereWebsiteId($data["website_id"])->firstOrFail();

            $data = $this->getCatalogInventoryItemData($data, $catalog_inventory);

            if ($data["quantity"] != 0) {
                $catalog_inventory_item = CatalogInventoryItem::create($data);
                $catalog_inventory->update([ "quantity" => $this->getNewQuantity($catalog_inventory, $catalog_inventory_item) ]);
            }
        }
        catch (Exception $exception)
        {
            DB::rollback();
            throw $exception;
        }

        DB::commit();
    }

    private function validateData(array $data): array
    {
        try
        {
            $validator = Validator::make($data, [
                "product_id" => "required|exists:products,id",
                "website_id" => "required|exists:websites,id",
                "quantity" => "required|decimal",
                "adjustment_type" => "required|in:addition,deduction",
                "event" => "required",
                "adjusted_by" => "sometimes|nullable|exists:admins,id",
                "order_id" => "sometimes|nullable|exists:orders,id"
            ]);
            if ( $validator->fails() ) throw ValidationException::withMessages(["logging_error" => $validator->errors()->toArray()]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $validator->validated();
    }

    private function getCatalogInventoryItemData(array $data, object $catalog_inventory): array
    {
        try
        {
            $data["catalog_inventory_id"] = $catalog_inventory->id;
            unset($data["product_id"], $data["website_id"]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    private function getNewQuantity(object $catalog_inventory, object $catalog_inventory_item): float
    {
        try
        {
            $new_quantity = (float) $catalog_inventory->quantity ?? 0;

            switch($catalog_inventory_item->adjustment_type) {
                case("addition"):
                    $new_quantity += $catalog_inventory_item->quantity;
                break;

                case("deduction"):
                    $new_quantity -= $catalog_inventory_item->quantity;
                break;
            }

            // if ( $new_quantity < 0 ) throw new InventoryCannotBeLessThanZero();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $new_quantity;
    }
}
