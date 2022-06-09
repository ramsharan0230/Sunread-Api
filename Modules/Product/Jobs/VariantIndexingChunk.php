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
use Modules\Product\Traits\ElasticSearch\ConfigurableProductHandler;

class VariantIndexingChunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConfigurableProductHandler;

    public $parent, $all_variants, $chunk_variant, $store;

    public function __construct(object $parent, object $all_variants, object $chunk_variant, object $store)
    {
        $this->parent = $parent;
        $this->all_variants = $all_variants;
        $this->chunk_variant = $chunk_variant;
        $this->store = $store;
    }

    public function handle(): void
    {
        try
        {
            $chunk_variant_batch = Bus::batch([])->onQueue("index")->dispatch();
            foreach ($this->chunk_variant as $variant) {
                $chunk_variant_batch->add(new VariantIndexing($this->parent, $this->all_variants, $variant, $this->store));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
