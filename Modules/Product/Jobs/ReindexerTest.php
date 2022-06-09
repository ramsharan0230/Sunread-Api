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
use Modules\Product\Jobs\BulkIndexing;

class ReindexerTest implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $store;

    public function __construct(?object $store = null)
    {
        $this->store = $store;
    }

    public function handle(): void
    {

        $chunk_products = Product::with([
            "variants",
            "categories",
            "product_attributes",
            "catalog_inventories",
            "attribute_options_child_products",
            "attribute_configurable_products",
            "website.stores",
            "images.types",
        ]);

        $chunk_products = $chunk_products->whereParentId(null)->get()->chunk(100);
        $product_batch = Bus::batch([])->onQueue("index")->dispatch();

        foreach ($chunk_products as $products)  {
            $product_batch->add(new BulkIndexing($products));
        }
    }
}
