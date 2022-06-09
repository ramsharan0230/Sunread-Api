<?php

namespace Modules\Product\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Facades\PriceFormat;

class VariantProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $scope = [
            "scope" => $request->scope ?? "website",
            "scope_id" => $request->scope_id ?? $this->website_id
        ];

        $images = $this->images()->get()->filter(fn ($img) => $img->types()->where("slug", "base_image")->first() )->first();
        
        $stock = $this->catalog_inventories()->first();

        $config_attributes = $this->attribute_options_child_products()->get();

        $price = $this->value(array_merge($scope, [ "attribute_slug" => "price" ]));

        return [
            "id" => $this->id,
            "name" => $this->value(array_merge($scope, [ "attribute_slug" => "name" ])),
            "type" => $this->type,
            "sku" => $this->sku,   
            "price" => PriceFormat::get($price, $scope["scope_id"], $scope["scope"]),       
            "stock" => (int) $stock?->quantity,
            "status" => (bool) $this->status,
            "visibility" => $this->value(array_merge($scope, [ "attribute_slug" => "visibility" ]))?->name,
            "images" => $images ? Storage::url($images->path) : null,
            "configurable_attributes" => $config_attributes->map(function($config_attribute) {
                $attribute_option = AttributeOption::find($config_attribute->attribute_option_id);
                return [
                    "attribute" => $attribute_option->attribute->name,
                    "attribute_option" => $attribute_option->name
                ];
            })->toArray(),
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
