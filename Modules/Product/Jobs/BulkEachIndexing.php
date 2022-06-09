<?php

namespace Modules\Product\Jobs;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Product\Entities\AttributeOptionsChildProduct;
use Modules\Product\Repositories\AttributeOptionsChildProductRepository;
use Modules\Product\Traits\ElasticSearch\ConfigurableProductHandler;
use Modules\Product\Traits\ElasticSearch\HasIndexing;

class BulkEachIndexing implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasIndexing;

    public $product;
    public $attributeOptionsChildProductRepository;

    public function __construct(object $product)
    {
        $this->product = $product;
        $this->attributeOptionsChildProductRepository = new AttributeOptionsChildProductRepository();
    }

    public function handle(): void
    {
        try
        {
            if ($this->product->type == "simple") {
                $this->bulkIndexingSimpleTest($this->product);
            }
            if ($this->product->type == "configurable") {

                $stores = $this->product->website->stores;

                $all_variants = $this->product->variants()->with([
                    "categories",
                    "product_attributes",
                    "catalog_inventories",
                    "attribute_options_child_products",
                    "images.types",
                ])->get();
                $variant_attribute_options = [];

                $options = $all_variants->map( function ($variant) {
                    return $variant->attribute_options_child_products->pluck("attribute_option_id");
                })->flatten(1)->unique()->toArray();

                foreach ($options as $option) {
                    $option_values = $this->attributeOptionsChildProductRepository->query(function ($query) use($all_variants, $option) {
                        return $query->whereIn("product_id", $all_variants->pluck("id")->toArray())
                            ->whereAttributeOptionId($option)
                            ->get();
                    });

                    foreach ($option_values as $option_value) {
                        if (!in_array($option, $variant_attribute_options) && !isset($variant_attribute_options[$option_value->product_id])) {
                            $variant_attribute_options[$option_value->product_id] = $option;
                        }
                    }
                }
                foreach ($stores as $store) {

                    $jobs[] = new StoreWiseBulkEachIndexing($this->product, $store, $all_variants, $variant_attribute_options);
                }
                Bus::chain($jobs)->onQueue("index")->dispatch();

                Log::info("Configurable Products", [
                    "message" => "Configurable products created successfully.",
                    "time" => now()->format("H:m:s"),
                ]);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
