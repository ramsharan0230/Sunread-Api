<?php

namespace Modules\NavigationMenu\Transformers\StoreFront;

use Illuminate\Http\Resources\Json\JsonResource;

class NavigationMenuResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "location" => $this->location,
            "website_id" => $this->website_id,
            "items" => $this->items,
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
