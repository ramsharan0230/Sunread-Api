<?php

namespace Modules\Product\Jobs;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Product\Traits\ElasticSearch\HasIndexing;

class SingleIndexing implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasIndexing;

    public $product, $store, $method;

    public function __construct(object $product, object $store, ?string $method = null)
    {
        $this->product = $product;
        $this->store = $store;
        $this->method = $method;
    }

    public function handle(): void
    {
        try
        {
            $this->singleIndexing($this->product, $this->store);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
