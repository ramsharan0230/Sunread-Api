<?php

namespace Modules\Inventory\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Entities\CatalogInventory;
use Modules\Product\Transformers\ProductResource;
use Modules\User\Transformers\AdminResource;

class CatalogInventoryItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "event" => $this->event,
            "order_id" => $this->order_id, // [TO::DO] include order resource 
            "adjustment_type" => $this->adjustment_type,
            "quantity" => $this->quantity,
            "catalog_inventory" => new CatalogInventoryResource($this->whenLoaded("catalog_inventory")),
            "adjusted_by" => new AdminResource($this->whenLoaded("admin")),
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
