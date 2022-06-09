<?php

namespace Modules\PaymentKlarna\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class KlarnaOrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "website_id" => $this->website_id,
            "store_id" => $this->store_id,
            "product_id" => $this->product_id,
            "order_id" => $this->order_id,
            "qty" => (float) $this->qty,
            "cost" => (float) $this->cost,
            "price" => (float)$this->price,
            "coupon_code" => $this->coupon_code,
            "row_total" => (float) $this->row_total,
            "row_total_incl_tax" => (float) $this->row_total_incl_tax,
            "tax_amount" => (float) $this->tax_amount,
            "tax_percent" => (float) $this->tax_percent,
            "discount_amount" => (float) $this->discount_amount,
            "discount_percent" => (float) $this->discount_percent,
            "discount_amount_tax" => (float) $this->discount_amount_tax,            
            "created_at" => $this->created_at?->format("M d, Y H:i A"),
            "updated_at" => $this->updated_at?->format("M d, Y H:i A")
        ];
    }
}
