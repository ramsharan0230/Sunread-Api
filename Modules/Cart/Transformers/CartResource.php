<?php

namespace Modules\Cart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Customer\Transformers\CustomerResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        // dd($this);
        return [
            "id" => $this->id,
            // "customer" => $this->whenLoaded(new CustomerResource($this->customer)),
            // "cart_items" => $this->whenLoaded(CartItemResource::collection($this->cartItems)),
            // "item_count" => $this->item_count,
            // "total_quantity" => $this->total_quantity,
            "coupon_code" => $this->coupon_code,
            "channel_code" => $this->channel_code,
            "store_code" => $this->store_code,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
