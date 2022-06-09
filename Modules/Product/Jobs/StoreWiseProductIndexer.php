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
use Modules\Product\Traits\ElasticSearch\HasIndexing;

class StoreWiseProductIndexer implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasIndexing;

    protected $product, $store;

    public function __construct(object $product, object $store)
    {
        $this->product = $product;
        $this->store = $store;
    }

    public function handle(): void
    {
        try
        {
            $product_batch = Bus::batch([])->onQueue("index")->dispatch();

            if ($this->product->type == "simple") $product_batch->add(new SingleIndexing($this->product, $this->store));

            
            if ($this->product->type == "configurable") {
                $all_variants = $this->product->variants()->with(["categories", "product_attributes", "catalog_inventories", "attribute_options_child_products"])->get();
                $chunk_variants = $all_variants->chunk(100);

                $product_batch->add(new ConfigurableIndexing($this->product, $this->store));
                foreach ( $chunk_variants as $chunk_variant )
                {
                    $chunk_variant_batch = Bus::batch([])->onQueue("index")->dispatch();
                    $chunk_variant_batch->add(new VariantIndexingChunk($this->product, $all_variants, $chunk_variant, $this->store));
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
