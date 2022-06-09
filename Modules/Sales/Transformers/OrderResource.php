<?php

namespace Modules\Sales\Transformers;

use Modules\Core\Facades\PriceFormat;
use Modules\Core\Transformers\StoreResource;
use Modules\Core\Transformers\WebsiteResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Customer\Transformers\CustomerResource;
use Modules\Sales\Transformers\OrderStatusResource;
use Modules\Sales\Transformers\OrderAddressResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        $order_meta = $this->order_metas?->filter(fn ($order_meta) => ($order_meta->meta_key == "{$this->payment_method}_response"))->first();
        return [
            "id" => $this->id,
            "order_items" => OrderItemResource::collection($this->whenLoaded("order_items")),
            "order_taxes" => OrderTaxResource::collection($this->whenLoaded("order_taxes")),
            "website" => new WebsiteResource($this->whenLoaded("website")),
            "store" => new StoreResource($this->whenLoaded("store")),
            "store_name" => $this->store_name,
            "channel_name" => $this->channel_name,
            "customer" => $this->when($this->customer_id, new CustomerResource($this->whenLoaded("customer"))),
            "is_guest" => (bool) $this->is_guest,
            "billing_address" => new OrderAddressResource($this->whenLoaded("billing_address")),
            "shipping_address" => new OrderAddressResource($this->whenLoaded("shipping_address")),
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
            "tax_amount" => PriceFormat::get($this->tax_amount, $this->store_id, "store"),
            "grand_total" => PriceFormat::get($this->grand_total, $this->store_id, "store"),
            "weight" => (float) $this->weight,
            "total_items_ordered" => (float) $this->total_items_ordered,
            "total_qty_ordered" => (float) $this->total_qty_ordered,
            "customer_email" => $this->customer_email,
            "customer_first_name" => $this->customer_first_name,
            "customer_middle_name" => $this->customer_middle_name,
            "customer_last_name" => $this->customer_last_name,
            "customer_phone" => $this->customer_phone,
            "customer_taxvat" => $this->customer_taxvat,
            "customer_ip_address" => $this->customer_ip_address,
            "status" => new OrderStatusResource($this->whenLoaded("order_status")),
            "create_account" => $request->create_account,
            "payment_response" => new OrderMetaResource($order_meta),
            "created_at" => $this->created_at?->format("M d, Y H:i A"),
            "updated_at" => $this->updated_at?->format("M d, Y H:i A")
        ];
    }
}
