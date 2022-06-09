<?php

namespace Modules\Erp\Traits;

use Exception;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Services\Pipe;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductImage;
use Modules\Erp\Entities\ErpImportDetail;
use Modules\Erp\Jobs\Mapper\ErpMigrateVariantJob;
use Modules\Erp\Jobs\Mapper\ErpMigrateVisibilityUpdateJob;
use Modules\Inventory\Entities\CatalogInventory;
use Modules\Erp\Jobs\Mapper\ErpMigratorJob;
use Modules\Erp\Traits\Mapper\AttributeMapper;
use Modules\Inventory\Entities\CatalogInventoryItem;
use Modules\Product\Entities\AttributeConfigurableProduct;
use Modules\Product\Entities\AttributeOptionsChildProduct;
use Modules\Product\Entities\ProductAttributeString;
use Illuminate\Support\Str;
use Modules\Erp\Facades\ErpLog;
use SebastianBergmann\Type\MixedType;

trait HasErpValueMapper
{
    use AttributeMapper;

    /**
     *
     * Import all the product from erp
     */
    public function importAll(): void
    {
        try
        {
            $chunked = ErpImportDetail::whereErpImportId(2)
                ->whereJsonContains("value->webAssortmentWeb_Active", true)
                ->whereJsonContains("value->webAssortmentWeb_Setup", "SR")
                ->get()
                ->unique("sku")
                ->chunk(100);
            foreach ($chunked as $chunk) {
                foreach ($chunk as $key => $detail) {
                    if ($detail->value["webAssortmentColor_Description"] == "SAMPLE") {
                        continue;
                    }
                    if ($detail->value["webAssortmentWeb_Active"] == false ) {
                        continue;
                    }
                    if ($detail->value["webAssortmentWeb_Setup"] != "SR") {
                        continue;
                    }

                    ErpMigratorJob::dispatch($detail)->onQueue("erp");
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    private function storeImages(object $product, object $erp_product_iteration, array $variant = []): void
    {
        try
        {
            $product_images = $this->getDetailCollection("productImages", $erp_product_iteration->sku);
            $images = $this->getValue($product_images, function ($value) {
                return is_array($value) ? $value : json_decode($value, true) ?? $value;
            });

            if (isset($variant["pfVerticalComponentCode"])) {
                $images = $images->where("color_code", $variant["pfVerticalComponentCode"]);
            }

            if ($images->count() > 0) {
                if ($product->type == "configurable") {
                    $configurable_images = [];
                    foreach ($images->groupBy("color_code") as $color_images) {
                        $configurable_images[] = $color_images->first();
                    }
                    $images = $configurable_images;
                }
                $position = 0;

                foreach ($images as $image) {
                    $generate_folder_name = Str::random(6);
                    $source_path = $image["url"];
                    $destination_path = "images/product/{$generate_folder_name}/{$product->sku}/{$image['image']}";
                    Storage::copy($source_path, $destination_path);
                    $data["path"] = $destination_path;
                    $data["position"] = $position;
                    $data["product_id"] = $product->id;
                    if ($position == 0) {
                        $type_ids = [1,2,3];
                    } else {
                        $type_ids = 5;
                    }
                    $position++;
                    $product_image = ProductImage::updateOrCreate($data);
                    $product_image->types()->sync($type_ids);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    /**
     *
     * Create variants based on parent product
     */
    private function createVariants(object $product, object $erp_product_iteration): void
    {
        try
        {
            $variants = $this->getDetailCollection("productVariants", $erp_product_iteration->sku);
            $webAssortments = $this->getDetailCollection("webAssortments", $this->detail->sku);
            $webAssortments = $this->getValue($webAssortments)->pluck("colorCode")->toArray();
            $variants = $this->getValue($variants)->whereIn("pfVerticalComponentCode", $webAssortments);

            if ($variants->count() >= 1) {
                $jobs = [];
                foreach ($variants as $variant) {
                    if ($variant["pfHorizontalComponentCode"] == "SAMPLE" || $variant["pfVerticalComponentCode"] == "SAMPLE") {
                        continue;
                    }

                    $jobs[] = new ErpMigrateVariantJob($product, $variant, $erp_product_iteration, $this->fetch_from_api);
                }

                Bus::batch($jobs)
                ->then(function (Batch $batch) use ($product) {
                    ErpMigrateVisibilityUpdateJob::dispatch($product)->onQueue('erp');
                    if ($this->fetch_from_api) {
                        ErpLog::webhookLog(
                            website_id: $product->website_id,
                            entity_type: "create",
                            entity_id: $product->sku,
                            payload: $product->toArray(),
                            is_processing: 0
                        );
                    }
                })->allowFailures()
                ->onQueue("erp")
                ->dispatch();

            }
        }
        catch (Exception $exception)
        {
            if ($this->fetch_from_api) {
                ErpLog::webhookLog(
                    website_id: $product->website_id,
                    entity_type: "create",
                    entity_id: $product->sku,
                    payload: $exception->getTrace(),
                    is_processing: 0,
                    status: 0
                );
            }
            throw $exception;
        }
    }

    /**
     *
     * Update visisbility of the configurable products.
     */
    private function updateVisibility(object $product): void
    {
        try
        {
            $attribute_configurable_product = AttributeConfigurableProduct::whereProductId($product->id)->get();
            if ($attribute_configurable_product->count() == 1) {
                optional($product->product_attributes
                    ->where("attribute_id", $this->getAttributeId("visibility"))
                    ->first())
                    ->value
                    ->update(["value" => $this->getAttributeOptionId("catalog_serach")]);
                $variant_products = Product::whereIn("id", $product->variants->pluck("id")->toArray())
                    ->with(["product_attributes"])
                    ->get();

                foreach ($variant_products as $variant_pro) {
                    $value_id = optional($variant_pro->product_attributes
                        ->where("attribute_id", $this->getAttributeId("visibility"))
                        ->first())
                        ->value
                        ->id;
                    Event::dispatch("catalog.products.update.before", $variant_pro->id);
                    ProductAttributeString::whereId($value_id)->update(["value" => $this->getAttributeOptionId("not_visible")]);
                    Event::dispatch("catalog.products.update.after", $variant_pro);
                }
            }
            $option_child_product_taken = new Pipe(new AttributeOptionsChildProduct());
            $attr_option_products = $option_child_product_taken->pipe($option_child_product_taken->value->whereIn("product_id", $product->variants->pluck("id")->toArray()))
                ->pipe($option_child_product_taken->value->with(["attribute_option", "attribute_option.attribute", "variant_product.product_attributes"]))
                ->pipe($option_child_product_taken->value->get())
                ->pipe($this->getFilterAttributeOptionColor($option_child_product_taken->value))
                ->pipe($option_child_product_taken->value->groupBy("attribute_option_id"))
                ->value;

            foreach ($attr_option_products as $attr_option_product) {
                foreach ($attr_option_product->pluck("variant_product")->sortBy("id") as $key => $variant_product) {
                    if ($key == 0) {
                        continue;
                    }
                    $value_id = optional($variant_product->product_attributes
                        ->where("attribute_id", $this->getAttributeId("visibility"))
                        ->first())
                        ->value
                        ->id;
                    Event::dispatch("catalog.products.update.before", $variant_product->id);
                    ProductAttributeString::whereId($value_id)->first()->update(["value" => $this->getAttributeOptionId("not_visible")]);
                    Event::dispatch("catalog.products.update.after", $variant_product);
                }
            }

        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    private function getFilterAttributeOptionColor(object $attribute_option): object
    {
        try
        {
            $filtered_options = $attribute_option->filter(function ($filter_attribute_option) {
                return $filter_attribute_option->attribute_option->attribute->id == $this->getAttributeId("color");
            });
        }
        catch (Exception $exception) {
            throw $exception;
        }

        return $filtered_options;
    }

    /**
     *
     * Create Inventory of product.
     */
    private function createInventory(object $product, object $erp_product_iteration, mixed $variant = null): mixed
    {
        try
        {
            $inventory = $this->getDetailCollection("webInventories", $erp_product_iteration->sku);

            if ($inventory->count() > 1 && $product->type == Product::SIMPLE_PRODUCT) {
                //TODO::Triggre notification on slack channel if there are  multiple inventory value
                $value = array_sum($this->getValue($inventory)->pluck("Inventory")->toArray());

                if ($variant) {
                    $inventory = $this->getValue($inventory)->where("Code", $variant["code"]);
                    $value = (float) array_sum($inventory->pluck("Inventory")->toArray());
                }

                $data = [
                    "quantity" => $value,
                    "use_config_manage_stock" => 1,
                    "product_id" => $product->id,
                    "website_id" => $product->website_id,
                    "manage_stock" =>  0,
                    "is_in_stock" => ($value > 0) ? 1 : 0,
                ];

                $match = [
                    "product_id" => $product->id,
                    "website_id" => $product->website_id,
                ];

                $catalog_inventory = CatalogInventory::updateOrCreate($match, $data);
                $adjustment_type = ($catalog_inventory->wasRecentlyCreated) ? "addition" : "deduction";
                $catalog_inventory_item_data = [
                    "catalog_inventory_id" => $catalog_inventory->id,
                    "event" => "erp.{$adjustment_type}",
                    "adjustment_type" => $adjustment_type,
                    "quantity" => $value,
                ];
                $catalog_inventory_item_match = [
                    "catalog_inventory_id" => $catalog_inventory->id,
                    "quantity" => $value,
                ];
                CatalogInventoryItem::updateOrCreate($catalog_inventory_item_match, $catalog_inventory_item_data);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $inventory;
    }
}
