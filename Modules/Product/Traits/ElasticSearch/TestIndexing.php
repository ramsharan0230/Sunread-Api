<?php

namespace Modules\Product\Traits\ElasticSearch;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Traits\Cacheable;
use Modules\Product\Entities\ImageType;
use Modules\Tax\Entities\ProductTaxGroup;

trait TestIndexing
{
    use Cacheable;

    protected $non_required_attributes = [ "cost" ];
    protected $options_fields = [ "select", "multiselect", "checkbox", "categoryselect" ];

    public function documentDataStructure(object $store, object $product): array
    {
        try
        {
            $fetched = [];
            $fetched = $this->storeCache("reindex", $product->id, function () use ($store, $product) {
                return $this->cacheData($product, $store);
            });

            $fetched = collect($fetched)->toArray();

            $fetched = $this->getProductAttributes($store, $product, $fetched);

            if ($product->type == "simple" && !$product->parent_id) {
                $fetched["list_status"] = (isset($fetched["visibility"]) && ($fetched["visibility"] == $fetched["visibility_check_id"])) ? 0 : 1;
            }

            unset($fetched["visibility_check_id"]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function getProductAttributes(object $store, object $product, $data): array
    {
        try
        {
            $channel_status = $product->channels->where("channel_id", $store?->channel_id)->first();
            if ($channel_status) {
                $data["status"] = 0;
            }

            $data["product_status"] = $data["status"];

            $attribute_slugs = ["name", "description", "color", "size", "visibility", "price", "tax_class_id", "url_key", "collection"];

            foreach ($attribute_slugs as $attribute_slug) {
                $attribute = Attribute::whereSlug($attribute_slug)->first();

                if (!$attribute) {
                    continue;
                }

                $match = [
                    "scope" => "store",
                    "scope_id" => $store->id,
                    "attribute_id" => $attribute->id
                ];

                if (in_array($attribute->type, $this->options_fields)) {
                    $values = $product->value($match);
                    $hasTranslation = $attribute->checkTranslation();
                    if ($values instanceof Collection) {
                        $data[$attribute->slug] = $values->pluck("id")->toArray();
                        foreach ($values as $key => $val) {
                            if ($hasTranslation) {
                                $translated_val = $val?->translations->where("store_id", $store->id)->first();
                            }
                            $data["{$attribute->slug}_{$key}_value"] = isset($translated_val) ? $translated_val->name : $val?->name;
                        }
                    } else {
                        $data[$attribute->slug] = $values?->id;
                        if ($hasTranslation) {
                            $translated_value = $values?->translations->where("store_id", $store->id)->first();
                        }
                        $data["{$attribute->slug}_value"] = isset($translated_value) ? $translated_value->name : $values?->name;

                        if ($attribute->slug == "visibility") {
                            $data["{$attribute->slug}_code"] = $values?->code;
                        }
                    }
                } else {
                    $data[$attribute->slug] = $product->value($match);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getAttributeOption(object $attribute, mixed $value): ?string
    {
        try
        {
            $attribute_option_class = $attribute->getConfigOption() ? new ProductTaxGroup() : new AttributeOption();
            $attribute_option = $attribute_option_class->find($value);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $attribute_option?->name;
    }

    public function getInventoryData(object $product): ?array
    {
        try
        {
            $data = [];
            $data = $product->catalog_inventories->first()?->only(["quantity", "is_in_stock"]);

            if ($data) {
                $data["stock_status_value"] = ($data["is_in_stock"] == 1) ? "In stock" : "Out of stock";
            } else {
                $data = [
                    "quantity" => 0,
                    "is_in_stock" => 0,
                    "stock_status_value" => "Out of stock",
                ];
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getCategoryData(object $store, object $product): array
    {
        try
        {
            $categories = $product->categories->map(function ($category) {
                return [
                    "id" => $category->id,
                    // "slug" => $category->value($defaul_data, "slug"),
                    // "name" => $category->value($defaul_data, "name")
                ];
            })->toArray();

        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $categories;
    }

    public function getImages(object $product): array
    {
        try
        {
            $image_types = ImageType::where("slug", "!=", "gallery")->get();
            foreach ($image_types as $image_type) {
                $images[$image_type->slug] = $this->getFullPath($product, $image_type->slug);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $images;
    }

    public function getFullPath($product, $image_name): ?array
    {
        try
        {
            $fetched = [];
            foreach ($product->images as $image) {
                $arr_types = $image->types->pluck("slug")->toArray();
                if ($image && in_array($image_name, $arr_types)) {
                    $fetched = [
                        "url" => $image->path ? Storage::url($image->path) : null,
                        "background_color" => $image?->background_color,
                        "background_size" => $image?->background_size,
                    ];
                    break;
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function cacheData(object $product, object $store): array
    {
        try
        {
            $fetched = [];
            $fetched = collect($product)->only(["id", "sku", "status", "website_id", "parent_id", "type"])->toArray();
            $inventory = $this->getInventoryData($product);
            $images = $this->getImages($product);

            $fetched = array_merge($fetched, $inventory, $images);

            $fetched['categories'] = $this->getCategoryData($store, $product);

            $visibility_att = Attribute::with(["attribute_options"])->whereSlug("visibility")->first();
            $visibility_id = $visibility_att->attribute_options->where("code", "not_visible")->first()?->id;
            $fetched["visibility_check_id"] = $visibility_id;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }
}
