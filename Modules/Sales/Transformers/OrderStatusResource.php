<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "order_state" => new OrderStatusStateResource($this->whenLoaded("order_status_state")),
            "name" => $this->name,
            "slug" => $this->slug,
            "created_at" => $this->created_at?->format("M d, Y H:i A")
        ];
    }
}
