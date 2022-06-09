<?php

namespace Modules\Page\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PageAttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "page" => new PageResource($this->whenLoaded("page")),
            "attribute" => $this->attribute,
            "value" => $this->value,
            "position" => $this->position
        ];
    }
}
