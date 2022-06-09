<?php

namespace Modules\Product\Listeners;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Entities\Website;
use Modules\Product\Jobs\ProductObserverIndexer;

class ProductListener
{
    public function indexing(object $product): void
    {
        $this->cacheClear($product);

        $batch = Bus::batch([])->onQueue("index")->dispatch();
        $batch->add(new ProductObserverIndexer($product)); 
    }

    public function remove(object $product): void
    {
        $this->cacheClear($product);

        $batch = Bus::batch([])->onQueue("index")->dispatch();
        $batch->add(new ProductObserverIndexer(collect($product), "delete")); 
    }

    public function cacheClear(object $product): void
    {
        $cache_name = "product_details_{$product->id}_*";
        $cache_key = Redis::keys($cache_name);
        if( count($cache_key) > 0 ) Redis::del($cache_key);
    }

}
