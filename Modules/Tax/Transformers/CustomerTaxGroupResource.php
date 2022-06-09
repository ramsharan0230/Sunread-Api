<?php

namespace Modules\Tax\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerTaxGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
