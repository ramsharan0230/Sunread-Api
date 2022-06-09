<?php

namespace Modules\Product\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Modules\Product\Entities\Product;

class ReindexMigrator implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $store;

    public function __construct(?object $store = null)
    {
        $this->store = $store;
    }

    public function handle(): void
    {
        $chunk_products = Product::with(["variants", "categories", "product_attributes", "catalog_inventories", "attribute_options_child_products"]);

        if($this->store) {
            $website = $this->store?->channel?->website;
            $chunk_products = $chunk_products->whereWebsiteId($website->id);
        }
        
        $chunk_products = $chunk_products->whereParentId(null)->get()->chunk(100);

        foreach ($chunk_products as $products)
        {
            $batch = Bus::batch([])->onQueue("index")->dispatch();
            foreach ($products as $product)
            {
                if($this->store) $batch->add(new StoreWiseProductIndexer($product, $this->store));
                else $batch->add(new ProductIndexer($product));
            }
        }
    }
}
