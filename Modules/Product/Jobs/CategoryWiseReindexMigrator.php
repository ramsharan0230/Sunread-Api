<?php

namespace Modules\Product\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;

class CategoryWiseReindexMigrator implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $products;

    public function __construct(object $products)
    {
        $this->products = $products;
    }

    public function handle(): void
    {     
        $chunk_products = $this->products->chunk(100);

        foreach ($chunk_products as $products)
        {
            $batch = Bus::batch([])->onQueue("index")->dispatch();
            foreach ($products as $product) $batch->add(new ProductObserverIndexer($product));
        }
    }
}
