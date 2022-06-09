<?php

namespace Modules\Attribute\Transformers\List;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeSetResource extends JsonResource
{
    public function toArray($request): array
    {
        $attribute_counts = $this->attribute_groups->map(function($attributeGroup){
            return $attributeGroup->attributes;
        })->flatten(1)->count();
        
        return [
            "id" => $this->id,
            "name" => $this->name,
            "is_user_defined" => $this->is_user_defined,
            "attribute_groups_count" => $this->attribute_groups->count(),
            "attributes_count" => $attribute_counts
        ];
    }
}
