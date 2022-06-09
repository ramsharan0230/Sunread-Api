<?php

namespace Modules\Page\Transformers\StoreFront;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Page\Transformers\PageResource;

class PageAttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "page" => new PageResource($this->whenLoaded("page")),
            "component" => $this->attribute,
            "attributes" => $this->value,
            "position" => $this->position
        ];
    }
}
