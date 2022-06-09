<?php

namespace Modules\Erp\Jobs\Mapper;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Jobs\Webhook\ErpProductUpdate;
use Modules\Product\Entities\Product;

class ErpProductAttributeUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        try
        {
            $chunked = Product::whereType(Product::CONFIGURABLE_PRODUCT)
                ->with(["product_attributes", "variants.product_attributes"])
                ->get()
                ->chunk(100);
            foreach ($chunked as $products) {
                foreach ($products as $product) {
                    ErpProductUpdate::dispatch(["sku" => $product->sku])->onQueue("erp");
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
