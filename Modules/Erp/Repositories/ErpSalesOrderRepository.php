<?php

namespace Modules\Erp\Repositories;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Modules\Core\Repositories\BaseRepository;
use Modules\Core\Services\Pipe;
use Modules\Core\Traits\ResponseMessage;
use Modules\Erp\Entities\ErpPaymentMethodMapper;
use Modules\Erp\Entities\ErpShippingAttributeMapper;
use Modules\Erp\Entities\NavErpOrderMapper;
use Modules\Erp\Facades\ErpLog;
use Modules\Product\Entities\Product;
use Modules\Sales\Entities\Order;
use Modules\Sales\Entities\OrderComment;
use Modules\Sales\Repositories\OrderMetaRepository;

class ErpSalesOrderRepository extends BaseRepository
{
    protected string $base_url;
    protected $orderMetaRepository;

    public function __construct()
    {
        $this->model = new Order();
        $this->model_name = "ErpOrder";
        $this->orderMetaRepository = new OrderMetaRepository();
        $this->base_url = config("erp_config.end_point");
        // $this->base_url = "https://bc.sportmanship.se:7148/sportmanshipbctestapi/api/NaviproAB/web/beta/";

    }

    private function httpResponse(): PendingRequest
    {
        try
        {
            // $response = Http::withBasicAuth("SPORTMANSHIP\\exthbg", "uFsR+z2862hZSWqcKt3ehPWpbakTSJ+OxQaFW/+MTUc=");
            $response = Http::withBasicAuth(config("erp_config.user_name"), config("erp_config.password"));
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $response;
    }

    public function post(Order $order): void
    {
        try
        {
            if (!$order->external_erp_id) {
                ErpLog::orderComment(
                    order: $order,
                    comment: __("core::app.erp.initialized-success", ["name" =>"Order webhook"])
                );

                $data = $this->getOrderData($order);
                $response = $this->httpResponse()
                    ->post("{$this->base_url}webSalesOrders", $data);
                $responseData = $response->throw()->json();

                if (array_key_exists("id", $responseData)) {
                    $orderItemResponses = $this->postOrderLines($order, $responseData);
                    $responseData["orderLines"] = $orderItemResponses;
                    $order->update(["external_erp_id" => $responseData["id"]]);
                }

                ErpLog::erpLog(
                    order: $order,
                    entity_id: $order->id,
                    event: "sales.order.erp-order.create",
                    type: "order",
                    request: $data,
                    response: $responseData,
                    response_code: $response->status()
                );
            }
        }
        catch (Exception $exception)
        {
            ErpLog::erpLog(
                order: $order,
                entity_id: $order->id,
                event: "sales.order.erp-order.create",
                type: "order",
                request: $this->getOrderData($order),
                response: $exception->response->json(),
                response_code: $exception->getCode()
            );
            $message = array_key_exists("error", $exception->response->json())
                ? "Erp webhook failed with status code {$exception->getCode()} and exception: {$exception->response->json()['error']['message']}"
                : "Erp Webhook failed with status code {$exception->getCode()} and exception: {$exception->getMessage()}";
            ErpLog::orderComment(
                order: $order,
                comment: $message,
                status_flag: OrderComment::STATUS_ERROR
            );
            throw $exception;
        }
    }

    public function postOrderLines(object $order, array $response): array
    {
        try
        {
            $orderItems = $this->getOrderLineData($order);
            $orderItemResponse = [];
            foreach ($orderItems as $orderItem) {
                $response = $this->httpResponse()
                    ->post("{$this->base_url}webSalesOrders({$response['id']})/webSalesOrderLines", $orderItem);
                $orderItemResponses[] = $response->throw()->json();
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $orderItemResponses;
    }

    public function getOrderData(object $order): array
    {
        try
        {
            $customerNavNumber = NavErpOrderMapper::whereCountryId($this->getOrderAddressData($order, "shipping")?->country_id)
                ->select("nav_customer_number")
                ->first();
            $paymentMethodMapper = ErpPaymentMethodMapper::wherePaymentMethod($order->payment_method)
                ->select("payment_method_code")->first();
            $shippingMethodMapper = ErpShippingAttributeMapper::whereShippingMethodCode($order->shipping_method)
                ->select("shipping_agent_code", "shipping_agent_service_code")
                ->first();
            $data = [
                "customerNumber" => $customerNavNumber?->nav_customer_number, //"JETSHOPTCDOMESTIC",
                "customerName" => $this->getOrderAddressData($order, "billing")?->full_name,
                "shipToName" => $this->getOrderAddressData($order, "shipping")?->full_name,
                "shippingAgentCode" => $shippingMethodMapper?->shipping_agent_code ?? "",//"DHL",
                "shippingAgentServicesCode" => $shippingMethodMapper?->shipping_agent_service_code ?? "",//"APC",
                "paymentMethodCode" => $paymentMethodMapper?->payment_method_code ?? "",
                "externalDocumentNumber" => (string) $order->id,
                "shippingPostalAddress" => json_encode($this->getShippingData($order, "shipping")), //$this->getShippingData($order, "shipping"), //
                "billingPostalAddress" => $this->getShippingData($order, "billing"),
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getOrderAddressData(object $order, string $type): ?object
    {
        return $order->order_addresses()
            ->where("address_type", $type)
            ->first();
    }

    public function getShippingData(object $order, string $type): array
    {
        try
        {
            $order_address = $this->getOrderAddressData($order, $type);
            $data = [
                "street" => $order_address?->address1,
                "city" => ($order_address?->city_id)
                    ? $order_address?->city?->name
                    : $order_address?->city_name,
                "state" => ($order_address?->region_id)
                    ? $order_address?->region?->name
                    : $order_address?->region_name,
                "countryLetterCode" => $order_address?->country?->code ?? "",
                "postalCode" => $order_address?->postcode,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getOrderLineData(object $order): array
    {
        try
        {
            $taken = new Pipe($order->order_items());
            $data = $taken->pipe($taken->value->get())
                ->pipe($this->getMappedOrderLineData($taken->value))
                ->pipe($taken->value->toArray())
                ->value;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getMappedOrderLineData(object $order_line): object
    {
        try
        {
            $data = $order_line->map(function ($order_item) {
                $product = $order_item->product()->first();
                $variantCode = $this->getAttributeValue(
                    store_id: $order_item->store()->first()?->id,
                    product: $product,
                    slug: "erp_variant_code"
                );
                $parent_product = ($product->type == Product::SIMPLE_PRODUCT && $product->parent_id)
                    ? $product->parent()->first()
                    : $product;
                $guid = $this->getAttributeValue(
                    store_id: $order_item->store()->first()->id,
                    product: $parent_product,
                    slug: "erp_guid"
                );

                //test data refrence
                //guuid: 68d55a49-45c6-4113-869a-640c0b7d1b9e
                //sku: 1711251
                //variantCode: 225S
                return [
                    "itemId" => $guid,
                    "variantCode" => $variantCode,
                    "lineType" => "Item",
                    // "unitOfMeasureId" => "e9180f3a-fb63-4d88-888d-fdef9d99ccad",
                    "quantity" => (float) $order_item->qty,
                    "unitPrice" => (float) $order_item->price,
                    "discountAmount" => (float) $order_item->discount_amount,
                    "discountPercent" => (float) $order_item->discount_percent,
                ];
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getAttributeValue(mixed $store_id, object $product, string $slug): mixed
    {
        return $product->value([
            "scope" => "store",
            "scope_id" => $store_id,
            "attribute_slug" => $slug,
        ]);
    }
}
