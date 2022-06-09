<?php

namespace Modules\Product\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "image" => $this->image_url,
            // "description" => $this->description,
            "translations" => $this->whenLoaded("translations"),
            "status" => (bool) $this->status,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
