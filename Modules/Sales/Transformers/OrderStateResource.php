<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderStateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "state" => $this->state,
            "is_default" => $this->is_default,
            "position" => $this->position,
            "created_at" => $this->created_at?->format("M d, Y H:i A"),
            "updated_at" => $this->updated_at?->format("M d, Y H:i A")
        ];
    }
}
