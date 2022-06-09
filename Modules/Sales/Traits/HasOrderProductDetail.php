<?php

namespace Modules\Sales\Traits;

use Exception;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\Attribute;
use Modules\Product\Entities\Product;

trait HasOrderProductDetail
{
    protected array $product_attribute_slug = [
        "name",
        "tax_class_id",
        "cost",
        "price",
        "weight",
        "tax_class_id",
        "special_to_date",
        "special_from_date",
    ];

    public function getProductDetail(object $request, array $data, ?callable $callback = null): ?object
    {
        try
        {
            $coreCache = $this->getCoreCache($request);
            $with = [
                "product_attributes",
                "catalog_inventories",
                "attribute_options_child_products.attribute_option.attribute",
                "images.types"
            ];
            $product = Product::whereId($data["product_id"])->with($with)->first();
            foreach ( $this->product_attribute_slug as $slug ) $data[$slug] = $this->getAttributeValue($coreCache, $product, $slug);
            if ($callback) $data = array_merge($data, $callback($product));
            $data = array_merge($data, ["sku" => $product->sku, "type" => $product->type]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return (object) $data;
    }

    public function getProductOptions(object $request, object $product): ?array
    {
        try
        {
            $coreCache = $this->getCoreCache($request);
            $image_path = $product->images?->filter(fn ($product_image) => in_array("base_image", $product_image->types->pluck("slug")->toArray()) )->first()?->path;
            $url = ($image_path) ? Storage::url($image_path) : null;

            if ($product->parent_id) {
                $product_options = [
                    "product_options" => [
                        "attributes" => $product->attribute_options_child_products
                        ->filter(fn ($child_product) => ($child_product->product_id == $product->id))
                        ->map(function ($child_product) {
                            return [
                                "attribute_id" => $child_product->attribute_option?->attribute_id,
                                "label" => $child_product->attribute_option?->attribute->name,
                                "name" => $child_product->attribute_option?->attribute->name,
                                "value" => $child_product->attribute_option?->name,
                            ];
                        })->toArray(),
                        "image_url" => $url
                    ]
                ];
            } else {
                $product_options = [
                    "product_options" => [
                        "attributes" => [
                            [
                                "attribute_id" => $this->attributeId("size"),
                                "label" => "size",
                                "name" => "Size",
                                "value" => $this->getAttributeValue($coreCache, $product, "size")?->name
                            ],
                            [
                                "attribute_id" => $this->attributeId("color"),
                                "label" => "color",
                                "name" => "Color",
                                "value" => $this->getAttributeValue($coreCache, $product, "color")?->name
                            ],
                        ],
                        "image_url" => $url
                    ]
                ];
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $product_options;
    }

    public function attributeId(string $slug): ?int
    {
        return Attribute::whereSlug($slug)->first()?->id;
    }

    public function getAttributeValue(mixed $coreCache, object $product, string $slug): mixed
    {
        return $product->value([
            "scope" => "store",
            "scope_id" => $coreCache->store->id,
            "attribute_slug" => $slug
        ]);
    }
}
