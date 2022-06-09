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
use Modules\Product\Traits\ElasticSearch\ConfigurableProductHandlerTest;

class StoreWiseBulkEachIndexing implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConfigurableProductHandlerTest;

    public $product;
    public $store;
    public $variants;
    public $variant_attribute_options;

    public function __construct(
        object $product,
        object $store,
        object $variants,
        array $variant_attribute_options
    ) {
        $this->product = $product;
        $this->store = $store;
        $this->variants = $variants;
        $this->variant_attribute_options = $variant_attribute_options;
    }

    public function handle(): void
    {
        try
        {
            $this->createProduct($this->product, $this->store, $this->variant_attribute_options);
            $chunk_variants = $this->variants->chunk(100);
            $chunk_variant_batch = Bus::batch([])->onQueue("index")->dispatch();
            foreach ($chunk_variants as $chunk_variant) {
                $chunk_variant_batch->add(new VariantIndexingTest($this->product, $this->variants, $chunk_variant, $this->store));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
