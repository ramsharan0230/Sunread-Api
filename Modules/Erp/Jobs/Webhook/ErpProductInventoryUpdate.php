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
use Modules\Erp\Traits\HasErpValueMapper;
use Modules\Product\Entities\Product;

class ErpProductInventoryUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasErpValueMapper;

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
            $product = Product::whereSku($this->data["sku"])->with(["product_attributes","variants"])->first();
            if ($product) {
                $this->website_id = $product->website_id;
                $data = (object) $this->data;
                $inventories = [];
                foreach ($product->variants as $variant) {
                    Event::dispatch("catalog.products.update.before", $variant->id);
                    $variant_code = [
                        "code" => optional($variant->product_attributes
                            ->where("attribute_id", $this->getAttributeId("erp_variant_code"))
                            ->first())
                            ->value_data,
                    ];
                    $inventories[] = $this->createInventory(
                        product: $variant,
                        erp_product_iteration: $data,
                        variant: $variant_code
                    )->toArray();
                    Event::dispatch("catalog.products.update.after", $variant);
                }

                ErpLog::webhookLog(
                    website_id: $product->website_id,
                    entity_type: "inventory",
                    entity_id: $product->sku,
                    payload: $inventories,
                    is_processing: 0,
                    status: 0
                );
            }
        }
        catch (Exception $exception)
        {
            ErpLog::webhookLog(
                website_id: $product->website_id,
                entity_type: "inventory",
                entity_id: $product->sku,
                payload: $exception->getTrace(),
                is_processing: 0,
                status: 0
            );
            throw $exception;
        }
    }
}
