<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderTaxItemsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "tax_percent" => $this->tax_percent,
            "amount" => (float) $this->amount,
            "tax_item_type" => $this->tax_item_type,
            "created_at" => $this->created_at?->format("M d, Y H:i A")
        ];
    }
}
