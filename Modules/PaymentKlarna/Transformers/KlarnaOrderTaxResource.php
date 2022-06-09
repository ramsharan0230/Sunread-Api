<?php

namespace Modules\PaymentKlarna\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\PaymentKlarna\Transformers\KlarnaOrderTaxItemsResource;

class KlarnaOrderTaxResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
           "id" => $this->id,
           "tax_items" => KlarnaOrderTaxItemsResource::collection($this->whenLoaded("order_tax_items")),
           "code" => $this->code,
           "title" => $this->title,
           "percent" => (float) $this->percent,
           "amount" => (float) $this->amount,
           "created_at" => $this->created_at?->format("M d, Y H:i A")
        ];
    }
}
