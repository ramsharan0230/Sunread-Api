<?php

namespace Modules\Erp\Jobs\Webhook;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Event;
use Modules\Erp\Facades\ErpLog;
use Modules\Erp\Traits\Mapper\PriceMapper;
use Modules\Product\Entities\Product;

class ErpProductPriceUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use PriceMapper;

    protected array $data;
    public int $website_id;
    public bool $fetch_from_api;
    public string $base_url;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->base_url = config("erp_config.end_point");
        $this->fetch_from_api = true;
    }

    public function handle(): void
    {
        try
        {
            $product = Product::whereSku($this->data["sku"])->with(["variants"])->first();
            $this->website_id = $product->website_id;
            $prices = $this->getDetailCollection("salePrices", $this->data["sku"]);
            ErpLog::webhookLog(
                website_id: $product->website_id,
                entity_type: "price",
                entity_id: $product->sku,
                payload: $prices->toArray(),
            );
            foreach ($product->variants as $variant) {
                Event::dispatch("catalog.products.update.before", $variant->id);
                $this->storeScopeWiseValue(
                    prices: $prices,
                    product: $variant
                );
                Event::dispatch("catalog.products.update.after", $variant);
            }
            ErpLog::webhookLog(
                website_id: $product->website_id,
                entity_type: "price",
                entity_id: $product->sku,
                payload: $prices->toArray(),
                is_processing: 0,
                status: 0
            );
        }
        catch (Exception $exception)
        {
            ErpLog::webhookLog(
                website_id: $product->website_id,
                entity_type: "price",
                entity_id: $product->sku,
                payload: $this->getDetailCollection("salePrices", $this->data["sku"])->toArray(),
                is_processing: 0,
                status: 0
            );
            throw $exception;
        }
    }
}
