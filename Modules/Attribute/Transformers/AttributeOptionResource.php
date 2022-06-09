<?php

namespace Modules\Attribute\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeOptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "position" => $this->position,
            "code" => $this->code,
            "translations" => $this->whenLoaded("translations")
        ];
    }
}
