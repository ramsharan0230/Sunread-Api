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
use Modules\Core\Entities\Website;
use Modules\Product\Traits\ElasticSearch\HasIndexing;

class ProductObserverIndexer implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasIndexing;

    protected $product, $method;

    public function __construct(object $product, ?string $method = null)
    {
        $this->product = $product;
        $this->method = $method;
    }

    public function handle(): void
    {
        try
        {
            if(!$this->method) {

                $stores = Website::find($this->product->website_id)->channels->map(function ($channel) {
                    return $channel->stores;
                })->flatten(1);
                $product_batch = Bus::batch([])->onQueue("index")->dispatch();

                foreach ($stores as $store)
                {
                    if ($this->product->type == "simple" && !$this->product->parent_id) $product_batch->add(new SingleIndexing($this->product, $store));
                    if ($this->product->type == "configurable") $product_batch->add(new ConfigurableIndexing($this->product, $store));
                    if ($this->product->type == "simple" && $this->product->parent_id) {
                        $siblings = $this->product->parent->variants()->with(["categories", "product_attributes", "catalog_inventories", "attribute_options_child_products"])->get();
                        $product_batch->add(new VariantIndexing($this->product->parent, $siblings, $this->product, $store));
                    }
                }
            }
            else {
                $stores = Website::find($this->product["website_id"])->channels->map(function ($channel) {
                    return $channel->stores;
                })->flatten(1);

                $remove_batch = Bus::batch([])->onQueue("index")->dispatch();
                foreach ($stores as $store) $remove_batch->add(new RemoveDocument($this->product, $store));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
