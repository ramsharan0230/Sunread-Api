<?php

namespace Modules\Category\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Transformers\ChannelResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        $this->createModel();
        $data = [
            "scope" => $request->scope ?? "website",
            "scope_id" => $request->scope_id ?? $request->website_id,
            "category_id" => $this->id,
            "attribute" => "name"
        ];
        $name = $this->has($data) ? $this->getValues($data) : $this->getDefaultValues($data);

        return [
            "id" => $this->id,
            "website_id" => $this->website_id,
            "parent_id" => $this->parent_id,
            "name" => $name?->value,

            "values" => $this->values,
            "channels" => ChannelResource::collection($this->whenLoaded("channels")),

            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
