<?php

namespace Modules\Product\Traits\ElasticSearch;

use Exception;
use Illuminate\Support\Facades\Storage;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Core\Entities\Website;
use Modules\Inventory\Entities\CatalogInventory;
use Modules\Product\Entities\AttributeConfigurableProduct;
use Modules\Product\Entities\AttributeOptionsChildProduct;
use Modules\Product\Entities\Product;

trait ConfigurableProductHandler
{
    use HasIndexing;

    public function createProduct(object $parent, object $store): void
    {
        try
        {
            $variants = $parent->variants()->with(["categories", "product_attributes", "catalog_inventories", "attribute_options_child_products"])->get();
            $variant_attribute_options = [];

            $options = $variants->map( function ($variant) {
                return $variant->attribute_options_child_products->pluck("attribute_option_id");
            })->flatten(1)->unique()->toArray();

            foreach($options as $option)
            {
                $option_values = AttributeOptionsChildProduct::whereIn("product_id", $variants->pluck("id")->toArray())->whereAttributeOptionId($option)->get();
                foreach($option_values as $option_value)
                {
                    if(!in_array($option, $variant_attribute_options) && !isset($variant_attribute_options[$option_value->product_id])) {
                        $variant_attribute_options[$option_value->product_id] = $option;
                    } 
                } 
            }

            $product_format = $parent->documentDataStructure($store); 
            $final_parent = array_merge($product_format, $this->getAttributeData(collect($variant_attribute_options), $parent, $store));

            if(count($final_parent) > 0) {
                $final_parent["list_status"] = ($this->checkVisibility($parent, $store)) ? 1 : 0;
                $this->configurableIndexing($final_parent, $store);   
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function createVariantProduct(object $parent, mixed $variants, object $variant, object $store): void
    {
        try
        {
            $product_format = $variant->documentDataStructure($store); 
            $configurable_attributes = $this->getConfigurableAttributes($variant, $store, $product_format);
            $product_format = array_merge($product_format, [ "show_configurable_attributes" => $configurable_attributes ]);
            

            if (!$this->checkVisibility($variant, $store)) {
                $product_format["list_status"] = 0;
                if(count($product_format) > 0) $this->configurableIndexing($product_format, $store);  
            }

            else {
                $group_by_attribute = AttributeConfigurableProduct::whereProductId($parent->id)->whereUsedInGrouping(1)->first();

                if($group_by_attribute) {

                    $is_group_attribute = $variant->value([
                        "scope" => "store",
                        "scope_id" => $store->id,
                        "attribute_id" => $group_by_attribute->attribute_id
                    ]);
        
                    $related_variants = AttributeOptionsChildProduct::whereIn("product_id", $variants->pluck("id")->toArray())->whereAttributeOptionId($is_group_attribute?->id)->get();
                    if($related_variants) {
                        $variant_attribute_options = AttributeOptionsChildProduct::whereIn("product_id", $related_variants->pluck("product_id")->toArray())->where("attribute_option_id", "!=", $is_group_attribute?->id)->get()->pluck("attribute_option_id", "product_id");
                    }
                }
                else $variant_attribute_options = $variant->attribute_options_child_products->pluck("attribute_option_id", "product_id");
    
                $final_variant = array_merge($product_format, $this->getAttributeData($variant_attribute_options, $variant, $store));  
                if(count($final_variant) > 0) $this->configurableIndexing($final_variant, $store); 
            }   
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function getAttributeData(object $variant_options, object $product, object $store): array
    {
        try
        {
            $items = [];
            $items["list_status"] = 1;

            $variant_options->map(function ($variant_option, $key) use (&$items, $product, $store) {
                $attribute_option = AttributeOption::find($variant_option);
                $attribute = $attribute_option->attribute;

                //check translation on attribute options
                $hasTranslation = $attribute->checkTranslation();
                if($hasTranslation) $translated_val = $attribute_option?->translations()->whereStoreId($store->id)->first();
                
                $items["configurable_{$attribute->slug}"][] = $variant_option;
                $items["configurable_{$attribute->slug}_value"][] = isset($translated_val) ? $translated_val->name : $attribute_option->name;
                
                $catalog = CatalogInventory::whereProductId($key)->first();
                $items["configurable_attributes"][$attribute->slug][] = [
                    "label" => isset($translated_val) ? $translated_val->name : $attribute_option->name,
                    "value" => $variant_option,
                    "product_id" => $key,
                    "stock_status" => ($catalog?->is_in_stock && $catalog?->quantity > 0) ? 1 : 0
                ];
                    
                if (isset($items[$attribute->slug]) && isset($items["{$attribute->slug}_value"])) {
                    unset($items[$attribute->slug], $items["{$attribute->slug}_value"]);
                }
            }); 
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $items;
    }

    public function checkVisibility(object $product, object $store): bool
    {
        try
        {
            $visibility = Attribute::whereSlug("visibility")->first();
            $visibility_option = AttributeOption::whereAttributeId($visibility?->id)->whereCode("not_visible")->first();

            $is_visibility = $product->value([
                "scope" => "store",
                "scope_id" => $store->id,
                "attribute_id" => $visibility?->id
            ]);
            
           $bool = ($is_visibility?->id != $visibility_option?->id);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $bool;
    }

    public function getConfigurableAttributes(object $variant, object $store, array $product_format): array
    {
        try
        {
            $configurable_attributes = $variant->attribute_options_child_products->map(function ($variant_option) use($variant, $store, $product_format) {
                $attribute_option = AttributeOption::find($variant_option->attribute_option_id);
                $attribute = $attribute_option->attribute;

                //check translation on attribute options
                $hasTranslation = $attribute->checkTranslation();
                if($hasTranslation) $translated_val = $attribute_option?->translations()->whereStoreId($store->id)->first();
                
                $catalog = CatalogInventory::whereProductId($variant->id)->first();
                $store_data = [
                    "scope" => "store",
                    "scope_id" => $store->id
                ];

                $visibility_data_val = $variant->value(array_merge($store_data, ["attribute_slug" => "visibility"]));
                return [
                    "id" => $variant_option->attribute_option_id,
                    "attribute_id" => $attribute->id,
                    "attribute_slug" => $attribute->slug,
                    "label" => isset($translated_val) ? $translated_val->name : $attribute_option->name,
                    "code" => $attribute_option->code ?? $attribute_option->name,
                    "product_id" => $variant->id,
                    "parent_id" => $variant->parent_id,
                    "product_sku" => $variant->sku,
                    "url_key" => $variant->value(array_merge($store_data, ["attribute_slug" => "url_key"])),
                    "visibility" => $visibility_data_val->id,
                    "stock_status" => ($catalog?->is_in_stock && $catalog?->quantity > 0) ? 1 : 0,
                    "product_status" => isset($product_format["product_status"]) ? $product_format["product_status"] : 1,
                    "attribute_combination" => $variant->attribute_options_child_products->pluck("attribute_option_id")->mapWithKeys( function($variant_att) {
                        $f_attribute_option = AttributeOption::find($variant_att);
                        return [
                            $f_attribute_option->attribute->slug => $variant_att
                        ];
                    }),
                    "image" => $this->getConfigurableImage($variant)
                ];
            })->toArray();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $configurable_attributes;
    }

    public function getConfigurableImage(object $product): ?array
    {
        try
        {
            $image = $product->images()->wherehas("types", function($query) {
                $query->whereSlug("small_image");
            })->first();
            if(!$image) {
                $image = $product->images()->wherehas("types", function($query) {
                    $query->whereSlug("base_image");
                })->first();
            }
            $path = $image ? Storage::url($image->path) : $image;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
         
        return [
            "url" => $path,
            "background_color" => $image?->background_color,
            "background_size" => $image?->background_size,
        ];
    }
}
