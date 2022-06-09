<?php

namespace Modules\Attribute\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        $configOptions = $this->getConfigOption();

        return [
            "id" => $this->id,
            "slug" => $this->slug,
            "name" => $this->name,
            "type" => $this->type,
            "scope" => $this->scope,
            "validation" => $this->validation,
            "full_validation" => $this->type_validation,
            "position" => $this->position,
            "is_required" => (bool) $this->is_required,
            "is_unique" => (bool) $this->is_unique,
            "is_searchable" => (bool) $this->is_searchable,
            "serach_weight" => $this->search_weight,
            "default_value" => $this->default_value,
            "use_in_layered_navigation" => (bool) $this->use_in_layered_navigation,
            "is_visible_on_storefront" => (bool) $this->is_visible_on_storefront,
            "is_user_defined" => (bool) $this->is_user_defined,
            "is_synchronized" => (bool) $this->is_synchronized,
            "comparable_on_storefront" => $this->comparable_on_storefront,
            "options" => $configOptions ?? AttributeOptionResource::collection($this->whenLoaded("attribute_options")),
            "translations" => $this->whenLoaded("translations"),
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
