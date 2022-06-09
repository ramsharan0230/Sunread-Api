<?php

namespace Modules\Erp\Jobs\Webhook;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Modules\Erp\Facades\ErpLog;
use Modules\Erp\Traits\Mapper\AttributeMapper;
use Modules\Product\Entities\Product;
use Symfony\Component\HttpFoundation\Response;

class ErpProductUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use AttributeMapper;

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
            $response = $this->httpGet("webItems?\$filter=no eq '{$this->data['sku']}'");
            if ($response->status() == Response::HTTP_OK) {
                $response = Arr::first($response->json()["value"]);
                $status = $response["webAssortmentWeb_Active"];
                $this->website_id = $product->website_id;
                $this->erpUpdateProductData($product);
                foreach ($product->variants as $variant) {
                    if ($status) {
                        $data = [
                            "itemNo" => $product->sku,
                            "pfVerticalComponentCode" => $variant->value([
                                "scope" => "website",
                                "scope_id" => $variant->website_id,
                                "attribute_slug" => "color",
                            ])->code
                        ];
                        $this->getProductStatus($variant, $data);
                    } else {
                        $variant->update(["status" => 0]);
                    }
                    $this->erpUpdateProductData($variant);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function erpUpdateProductData(object $product): void
    {
        try
        {
            Event::dispatch("catalog.products.update.before", $product->id);
            $this->createAttributeValue(product: $product, callback: function () use ($product) {
                return $this->getUpdateAttributeData($product, $this->data["sku"]);
            });

            $description_value = $this->getDetailCollection("productDescriptions", $this->data["sku"]);
            if ($description_value->count() > 0) {
                $this->getScopeWiseDescription(
                    description_value: $description_value,
                    product: $product,
                    erp_product_iteration: (object) [
                        "sku" => $this->data["sku"],
                    ]
                );
            }
            Event::dispatch("catalog.products.update.after", $product);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function getUpdateAttributeData(object $product, string $sku): array
    {
        try
        {
            $ean_codes = $this->getDetailCollection("eanCodes", $sku);
            $variant_code = optional($product->product_attributes
                ->where("attribute_id", $this->getAttributeId("erp_variant_code"))->first())
                ->value_data;
            $ean_code = $this->getValue($ean_codes)
                ->where("variantCode", $variant_code)
                ->first();
            $ean_code_value = !empty($ean_code) ? $ean_code["crossReferenceNo"] : "";
            $data = [
                [
                    "attribute_id" => $this->getAttributeId("erp_features"),
                    "value" => $this->getAttributeValue(
                        erp_product_iteration: (object) ["sku" => $sku],
                        attribute_name: "Features"
                    ),
                ],
                [
                    "attribute_id" => $this->getAttributeId("size_and_care"),
                    "value" => $this->getAttributeValue(
                        erp_product_iteration: (object) ["sku" => $sku],
                        attribute_name: "Size and care"
                    ),
                ],
                [
                    "attribute_id" => $this->getAttributeId("ean_code"),
                    "value" => $ean_code_value,
                ],
            ];

            if ($product->type == Product::CONFIGURABLE_PRODUCT) {
                ErpLog::webhookLog(
                    website_id: $product->website_id,
                    entity_type: "update",
                    entity_id: $product->sku,
                    payload: $data,
                    is_processing: 0,
                    status: 0
                );
            }
        }
        catch (Exception $exception)
        {
            ErpLog::webhookLog(
                website_id: $product->website_id,
                entity_type: "update",
                entity_id: $product->parent->sku,
                payload: $exception->getTrace(),
                is_processing: 0,
                status: 0
            );
            throw $exception;
        }

        return $data;
    }
}
