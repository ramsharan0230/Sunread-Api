<?php

namespace Modules\Erp\Jobs\Mapper;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Traits\HasErpValueMapper;
use Modules\Erp\Traits\HasStorageMapper;
use Modules\Product\Entities\Product;
use Modules\Product\Repositories\ProductImageRepository;

class ErpProductImageUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasStorageMapper;
    use HasErpValueMapper;

    public $repository;

    public function __construct()
    {
        $this->repository = new ProductImageRepository();
    }

    public function handle(): void
    {
        $images = $this->getImageData();
        $product_chunked = Product::whereType(Product::CONFIGURABLE_PRODUCT)
            ->with(["variants.images", "images"])
            ->get()
            ->chunk(100);
        foreach ($product_chunked as $products) {
            foreach ($products as $product) {
                $product_images = $images->where("sku", $product->sku);
                if ($product_images->count() > 0) {
                    ImageMigrate::dispatch($product, $product_images)->onQueue("erp");
                    foreach ($product->variants as $variant) {
                        ImageMigrate::dispatch($variant, $product_images)->onQueue("erp");
                    }
                }
            }
        }
    }

}
