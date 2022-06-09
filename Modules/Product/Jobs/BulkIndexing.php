<?php

namespace Modules\Product\Jobs;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Product\Traits\ElasticSearch\ConfigurableProductHandler;
use Modules\Product\Jobs\BulkEachIndexing;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class BulkIndexing implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $products;

    public function __construct(object $products)
    {
        $this->products = $products;
    }

    public function handle(): void
    {
        try
        {
            Log::info("Chunk Products", [
                "message" => "Product are chunked ",
                "time" => now()->format("H:m:s"),
            ]);

            $product_batch = Bus::batch([])->onQueue("index")->dispatch();
            foreach ($this->products as $product) {
                $product_batch->add(new BulkEachIndexing($product));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
