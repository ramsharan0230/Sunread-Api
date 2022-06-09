<?php

namespace Modules\NavigationMenu\Transformers\List;

use Modules\Core\Facades\CoreCache;
use Illuminate\Http\Resources\Json\JsonResource;

class NavigationMenuItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            "scope" => $request->scope ?? "website",
            "scope_id" => $request->scope_id ?? $this->navigationMenu->website_id,
        ];
        return  [
            "id" => $this->id,
            "scope" => $this->scope,
            "scope_id" => $this->scope_id,
            "attribute" => $this->attribute,
            "title" => $this->value($data, "title"),
            "values" => $this->values,
            "children" => NavigationMenuItemResource::collection($this->children),
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }

}
