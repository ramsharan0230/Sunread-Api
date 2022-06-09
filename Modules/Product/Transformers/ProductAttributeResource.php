<?php

namespace Modules\Product\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "attribute" => [
                "id" => $this->attribute()->first()->id,
                "name" => $this->attribute()->first()->name,
                "slug" => $this->attribute()->first()->slug,
                "type" => $this->attribute()->first()->type
            ],
            "value" => $this->value_data
        ];
    }
}
