<?php

namespace Modules\PaymentKlarna\Transformers;

use Modules\Core\Transformers\StoreResource;
use Modules\Core\Transformers\ChannelResource;
use Modules\Core\Transformers\WebsiteResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Customer\Transformers\CustomerResource;
use Modules\PaymentKlarna\Transformers\KlarnaOrderTaxResource;
use Modules\PaymentKlarna\Transformers\KlarnaOrderItemResource;

class KlarnaOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "order_items" => KlarnaOrderItemResource::collection($this->whenLoaded("order_items")),
            "order_taxes" => KlarnaOrderTaxResource::collection($this->whenLoaded("order_taxes")),
            "website" => new WebsiteResource($this->whenLoaded("website")),
            "store" => new StoreResource($this->whenLoaded("store")),
            "channel" => new ChannelResource($this->whenLoaded("channel")),
            "customer" => $this->when($this->customer_id, new CustomerResource($this->whenLoaded("customer"))),
            "klarna_api_order_id" => $this->klarna_api_order_id,
            "shipping_method" => $this->shipping_method,
            "shipping_method_label" => $this->shipping_method_label,
            "payment_method" => $this->payment_method,
            "payment_method_label" => $this->payment_method_label,
            "currency_code" => $this->currency_code,
            "discount_amount" => (float) $this->discount_amount,
            "discount_amount_tax" => (float) $this->discount_amount_tax,
            "shipping_amount" => (float) $this->shipping_amount,
            "shipping_amount_tax" => (float) $this->shipping_amount_tax,
            "sub_total" => (float) $this->sub_total,
            "sub_total_tax_amount" => (float) $this->sub_total_tax_amount,
            "tax_amount" => (float) $this->tax_amount,
            "grand_total" => (float) $this->grand_total,
            "weight" => (float) $this->weight,
            "total_item_ordered" => (float) $this->total_item_ordered,
            "total_qty_ordered" => (float) $this->total_qty_ordered,
            "customer_ip_address" => $this->customer_ip_address,
            "status" => $this->status,
            "klarna_response" => $this->klarna_response,
            "created_at" => $this->created_at?->format("M d, Y H:i A"),
            "updated_at" => $this->updated_at?->format("M d, Y H:i A")
        ];
    }
}
