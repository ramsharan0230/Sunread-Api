<?php

namespace Modules\Category\Transformers\List;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Transformers\ChannelResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            "scope" => $request->scope ?? "website",
            "scope_id" => $request->scope_id ?? $request->website_id ?? $this->website_id
        ];

        return [
            "id" => $this->id,
            "slug" => $this->value($data, "slug"),
            "name" => $this->value($data, "name"),
            "children" => CategoryResource::collection($this->whenLoaded("children")->sortBy('_lft'))
        ];
    }
}
