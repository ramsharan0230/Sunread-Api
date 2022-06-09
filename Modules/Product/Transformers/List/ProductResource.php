<?php

namespace Modules\Product\Transformers\List;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Category\Transformers\CategoryResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $scope = [
            "scope" => $request->scope ?? "website",
            "scope_id" => $request->scope_id ?? $this->website_id
        ];

        $images = $this->images()->get()->filter(fn ($img) => $img->types()->where("slug", "base_image")->first() )->first();
        
        $stock = $this->catalog_inventories()->first();

        return [
            "id" => $this->id,
            "name" => $this->value(array_merge($scope, [ "attribute_slug" => "name" ])),
            "type" => $this->type,
            "sku" => $this->sku,           
            "stock" => (int) $stock?->quantity,
            "status" => (bool) $this->status,
            "categories" => CategoryResource::collection($this->whenLoaded("categories")),
            "visibility" => $this->value(array_merge($scope, [ "attribute_slug" => "visibility" ]))?->name,
            "images" => $images ? Storage::url($images->path) : null,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
