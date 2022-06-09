<?php

namespace Modules\Page\Transformers\StoreFront;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "slug" => $this->slug,
            "title" => $this->title,
            "position" => $this->position,
            "status" => (bool) $this->status,
            "meta_title" => $this->meta_title,
            "meta_description" => $this->meta_description,
            "meta_keywords" => $this->meta_keywords,
            "layout_type" => $this->layout_type,
            // "components" => PageAttributeResource::collection($this->whenLoaded("page_attributes"))
        ];
    }
}
