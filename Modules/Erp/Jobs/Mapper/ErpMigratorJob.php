<?php

namespace Modules\Erp\Jobs\Mapper;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Traits\HasErpValueMapper;
use Modules\Product\Entities\Product;
use Modules\Product\Repositories\ProductAttributeStringRepository;

class ErpMigratorJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasErpValueMapper;

    public $tries = 3;
    public $timeout = 1200;

    protected object $detail;
    public $repository;
    public int $website_id;
    public bool $fetch_from_api;
    public string $base_url;

    public function __construct(object $detail, ?bool $fetch_from_api = false)
    {
        $this->detail = $detail;
        $this->repository = new ProductAttributeStringRepository();
        $this->website_id = $detail->website_id ?? 1;
        $this->base_url = config("erp_config.end_point");
        $this->fetch_from_api = $fetch_from_api;
    }

    public function handle(): void
    {
        try
        {
            $variants = $this->getDetailCollection("productVariants", $this->detail->sku);
            $webAssortments = $this->getDetailCollection("webAssortments", $this->detail->sku);
            $webAssortments = $this->getValue($webAssortments)->pluck("colorCode")->toArray();
            $check_migratable_variants = ($this->getValue($variants)->whereIn("pfVerticalComponentCode", $webAssortments)->count() >= 1);
            if ($check_migratable_variants) {
                $match = [
                    "website_id" => $this->website_id,
                    "sku" => $this->detail->sku,
                ];
                $product_data = array_merge($match, [
                    "attribute_set_id" => 1,
                    "type" => Product::CONFIGURABLE_PRODUCT,
                    "parent_id" => null,
                ]);

                $product = Product::updateOrCreate($match, $product_data);
                if (!$this->fetch_from_api) {
                    $product->categories()->sync(3);
                }

                $this->createAttributeValue($product, $this->detail, false, 5);
                $this->createVariants($product, $this->detail);
                $this->storeImages($product, $this->detail);
                $this->createInventory($product, $this->detail);
                Event::dispatch("catalog.products.create.after", $product);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
