<?php

namespace Modules\Page\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Transformers\WebsiteResource;

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
            "website" => new WebsiteResource($this->whenLoaded("website")),
            "layout_type" => $this->layout_type,
            "scopes" => PageScopeResource::collection($this->whenLoaded("page_scopes")),
            "components" => PageAttributeResource::collection($this->whenLoaded("page_attributes")),
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
