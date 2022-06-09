<?php

namespace Modules\Category\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Transformers\StoreResource;

class CategoryValueResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "scope" => $this->scope,
            "scope_id" => $this->scope_id,
            "attribute" => $this->attribute,
            "value" => $this->value,
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
