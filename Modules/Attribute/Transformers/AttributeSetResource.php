<?php

namespace Modules\Attribute\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Attribute\Entities\Attribute;

class AttributeSetResource extends JsonResource
{
    public function toArray($request): array
    {
        $attribute_ids = $this->attribute_groups->map(function($attributeGroup){
            return $attributeGroup->attributes->pluck('id');
        })->flatten(1)->toArray();
        
        return [
            "id" => $this->id,
            "name" => $this->name,
            "is_user_defined" => $this->is_user_defined,
            "groups" => AttributeGroupResource::collection($this->whenLoaded("attribute_groups")->sortBy('position')),
            "unassigned_attributes" => AttributeResource::collection(Attribute::whereNotIn('id', $attribute_ids)->get())
        ];
    }
}
