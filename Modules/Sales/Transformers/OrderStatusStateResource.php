<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusStateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "state" => $this->state,
            "is_default" => $this->is_default,
            "position" => $this->position,
            "created_at" => $this->created_at?->format("M d, Y H:i A")
        ];
    }
}
