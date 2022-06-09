<?php

namespace Modules\Product\Transformers\StoreFront;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Entities\Store;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Facades\PriceFormat;
use Modules\Product\Entities\AttributeOptionsChildProduct;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $store = Store::whereCode($request->header("hc-store"))->first();
        $scope = [
            "scope" => "store",
            "scope_id" => $store->id
        ];
        $price = $this->value(array_merge($scope, [ "attribute_slug" => "price" ]));
        $image = $this->images()->get()->filter(fn ($img) => $img->types()->where("slug", "base_image")->first() )->first();
        $sizes = [];

        if($this->parent_id) {
            $color = $this->value(array_merge($scope, [ "attribute_slug" => "color" ]));
            if($color) {
                $attribute_option_variants = AttributeOptionsChildProduct::whereIn("product_id", $this->parent->variants->pluck("id")->toArray())->get();
                $color_variants = $attribute_option_variants->where("attribute_option_id", $color->id)->pluck("product_id")->toArray();
                $size_options = AttributeOptionsChildProduct::with(["attribute_option", "variant_product"])->whereIn("product_id", $color_variants)->where("attribute_option_id", "!=", $color->id)->get();
                foreach($size_options as $size_option)
                {
                    $size_stock = $size_option->variant_product->catalog_inventories()->first();

                    $sizes[] = [
                        "product_id" => $size_option?->product_id,
                        "size" => $size_option?->attribute_option?->name,
                        "stock_status" => $size_stock?->is_in_stock,
                    ];
            }
        }
        }

        return [
            "id" => $this->id,
            "name" => $this->value(array_merge($scope, [ "attribute_slug" => "name" ])),
            "price" => $price,
            "price_formatted" => PriceFormat::get($price, $store->id, "store"),
            "url_key" => $this->value(array_merge($scope, [ "attribute_slug" => "url_key" ])),
            "type" => $this->type,
            "sku" => $this->sku,
            "status" => (bool) $this->status,
            "description" => $this->value(array_merge($scope, [ "attribute_slug" => "description" ])),
            "size" => $sizes,
            "image" => $image ? Storage::url($image->path) : $image,
            "color" => $this->value(array_merge($scope, [ "attribute_slug" => "color" ])),
        ];
    }
}
