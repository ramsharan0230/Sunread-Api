<?php

namespace Modules\Product\Jobs;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Product\Traits\ElasticSearch\ConfigurableProductHandlerTest;

class VariantIndexingTest implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConfigurableProductHandlerTest;

    public $parent, $variants, $chunk_variant, $store;

    public function __construct(
        object $parent,
        object $variants,
        object $chunk_variant,
        object $store
    ) {
        $this->parent = $parent;
        $this->variants = $variants;
        $this->chunk_variant = $chunk_variant;
        $this->store = $store;
    }

    public function handle(): void
    {
        try
        {
            foreach ($this->chunk_variant as $variant) {
                $this->createVariantProduct($this->parent, $this->variants, $variant, $this->store);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
