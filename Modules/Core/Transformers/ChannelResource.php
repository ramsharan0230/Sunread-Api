<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Category\Transformers\CategoryResource;

class ChannelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "code" => $this->code,
            "hostname" => $this->hostname,
            "description" => $this->description,
            "default_store" => new StoreResource($this->whenLoaded("default_store")),
            "stores" => StoreResource::collection($this->whenLoaded("stores")),
            "stores_count" => $this->when($this->relationLoaded("stores"), $this->stores->count()),
            "website" => new WebsiteResource($this->whenLoaded("website")),
            "status" => (bool) $this->status,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
