<?php

namespace Modules\Cart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\Transformers\ProductResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            // "product" => $this->whenLoaded(new ProductResource($this->product)),
            // "cart" => $this->whenLoaded(new CartResource($this->cart)),
            "qty" => $this->qty,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
