<?php

namespace Modules\Product\Jobs;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use Modules\Product\Traits\ElasticSearch\ConfigurableProductHandler;

class VariantIndexing implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConfigurableProductHandler;

    public $parent, $variants, $variant, $store;

    public function __construct(object $parent, object $variants, object $variant, object $store)
    {
        $this->parent = $parent;
        $this->variants = $variants;
        $this->variant = $variant;
        $this->store = $store;
    }

    public function handle(): void
    {
        try
        {
            $data = json_decode(Redis::get("reindexer_{$this->variant->id}"));
            $data = collect($data)->toArray();
            if (count($data)  > 0) {
                $this->configurableIndexing($data, $this->store);   
            } else {
                $this->createVariantProduct($this->parent, $this->variants, $this->variant, $this->store);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
