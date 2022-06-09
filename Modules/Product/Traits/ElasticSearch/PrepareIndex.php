<?php

namespace Modules\Product\Traits\ElasticSearch;

use Exception;
use Illuminate\Support\Facades\Bus;
use Modules\Core\Entities\Website;
use Modules\Product\Jobs\SingleIndexing;

trait PrepareIndex
{
    public function preparingIndexData(object $products, ?string $method = null): void
    {
        try
        {
            $batch = Bus::batch([])->onQueue("index")->dispatch();
            foreach($products as $product) $this->preparingSingleData($product, $batch, $method);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    } 

    public function preparingSingleData(object $product, object $batch, ?string $method = null): void
    {
        try
        {
            $stores = Website::find($product->website_id)->channels->map(function ($channel) {
                return $channel->stores;
            })->flatten(1);
            
            foreach($stores as $store) {
                if(!$method) $this->prepareIndexing($product, $batch, $store);
                else $this->prepareRemoving($product, $batch, $store);
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    } 

    public function prepareIndexing(object $product, object $batch, object $store): void
    {
        try
        {
            $batch->add(new SingleIndexing($product, $store));
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    } 

    public function prepareRemoving(object $product, object $batch, object $store): void
    {
        try
        {
            $batch->add(new SingleIndexing(collect($product), $store, "delete"));
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    } 

}
