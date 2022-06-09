<?php

namespace Modules\NavigationMenu\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class NavigationMenuItemResource extends JsonResource
{
    public function toArray($request): array
    {

        $data = [
            "scope" => $request->scope ?? "website",
            "scope_id" => $request->scope_id ?? $this->navigationMenu->website_id,
        ];

        return [
            "id" => $this->id,
            "navigation_menu_id" => $this->navigation_menu_id,
            "navigation_menu_title" => $this->navigationMenu->title,
            "title" => $this->value($data, "title"),
            "page_id" => $this->value($data, "page_id"),
            "category_id" => $this->value($data, "category_id"),
            "custom_link" => $this->value($data, "custom_link"),
            "dynamic_link" => $this->value($data, "dynamic_link"),
            "type" => $this->value($data, "type"),
            "status" => $this->value($data, "status"),
            "parent_id" => $this->parent_id,
            "position" => $this->position,
            "values" => $this->values,
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
