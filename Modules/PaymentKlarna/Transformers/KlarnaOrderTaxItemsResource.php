<?php

namespace Modules\PaymentKlarna\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class KlarnaOrderTaxItemsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "tax_percent" => $this->tax_percent,
            "amount" => (float) $this->amount,
            "tax_item_type" => (float) $this->tax_item_type,
            "created_at" => $this->created_at?->format("M d, Y H:i A")
        ];
    }
}
