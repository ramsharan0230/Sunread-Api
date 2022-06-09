<?php

namespace Modules\Page\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PageScopeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "page" => new PageResource($this->whenLoaded("page")),
            "scope" => $this->scope,
            "scope_id" => $this->scope_id
        ];
    }
}
