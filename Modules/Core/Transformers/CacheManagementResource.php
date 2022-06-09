<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CacheManagementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "description" => $this->description,
            "tag" => $this->tag,
            "key" => $this->key,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
