<?php

namespace Modules\Sales\Transformers;

use Modules\Core\Facades\PriceFormat;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "website_id" => $this->website_id,
            "store_id" => $this->store_id,
            "product_id" => $this->product_id,
            "order_id" => $this->order_id,
            "currency_code" => $this->order->currency_code,
            "product_options" => $this->product_options,
            "product_type" => $this->product_type,
            "sku" => $this->sku,
            "name" => $this->name,
            "weight" => $this->weight,
            "qty" => (float) $this->qty,
            "cost" => (float) $this->cost,
            "price" => $this->price,
            "total_amount_formatted" => $this->item_total_price(),
            "price_incl_tax" => (float) $this->price_incl_tax,
            "coupon_code" => $this->coupon_code,
            "discount_amount" => (float) $this->discount_amount,
            "discount_percent" => (float) $this->discount_percent,
            "discount_amount_tax" => (float) $this->discount_amount_tax,
            "tax_amount" => (float) $this->tax_amount,
            "tax_percent" => (float) $this->tax_percent,
            "row_total" => (float) $this->row_total,
            "row_total_incl_tax" => (float) $this->row_total_incl_tax,
            "row_weight" => (float) $this->row_weight,
            "created_at" => $this->created_at?->format("M d, Y H:i A"),
            "updated_at" => $this->updated_at?->format("M d, Y H:i A"),
        ];
    }

    private function item_total_price()
    {
        $total_amount_formatted = $this->price_incl_tax * $this->qty;
        return PriceFormat::get($total_amount_formatted, $this->store_id, "store");
    }
}
