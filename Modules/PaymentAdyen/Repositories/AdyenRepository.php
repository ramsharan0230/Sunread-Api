<?php

namespace Modules\PaymentAdyen\Repositories;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\Facades\SiteConfig;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Modules\PaymentAdyen\Traits\AdyenApiConfiguration;
use Modules\CheckOutMethods\Contracts\PaymentMethodInterface;
use Modules\CheckOutMethods\Repositories\BasePaymentMethodRepository;

class AdyenRepository extends BasePaymentMethodRepository implements PaymentMethodInterface
{
    use AdyenApiConfiguration;

    protected object $request;
    protected object $parameter;
    protected string $method_key;

    public function __construct(object $request, object $parameter)
    {
        $this->request = $request;
        $this->method_key = "adyen";
        $this->parameter = $parameter;

        parent::__construct($this->request, $this->method_key, $this->parameter);
        $this->urls = $this->getApiUrl();
    }

    public function get(): mixed
    {
        DB::beginTransaction();
        try 
        {
            $this->baseData = $this->getBaseData($this->coreCache);
            $config_data = $this->baseData;
            $this->headers = array_merge($this->headers, [
                "Content-Type" => "application/json",
                "X-API-Key" => $config_data->api_key
            ]);

            $this->base_url = $this->getBaseUrl();
            $data =  $this->getPostData($config_data);
            $response = $this->postClient("v68/sessions", $data);
            $this->orderRepository->update([
                "payment_method" => $this->method_key,
                "payment_method_label" => $config_data->payment_method_label,
                "status" => $config_data->status?->slug,
            ], $this->parameter->order->id, function ($order) use ($response, $config_data) {
                $match = [
                    "order_id" => $order->id,
                    "meta_key" => "{$this->method_key}_response",
                ];
                $data = array_merge($match, [
                    "meta_value" => [
                        "clientKey" => $config_data->client_key,
                        "environment" => $config_data->environment,
                        "response" => $response,
                    ]
                ]);
                $this->orderMetaRepository->createOrUpdate($match, $data);
            });

            Redis::set($this->parameter->order->cart_id, json_encode($this->request->create_account));
            $transaction_log = [
                "order" => $this->parameter->order,
                "request" => $data,
                "response" => $response,
            ];
            Event::dispatch("order.transaction.create.update.after", $transaction_log);
        }
        catch ( Exception $exception )
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return $response;
    }

    private function getPostData(object $config_data): array
    {
        try
        {
            $order = $this->orderModel->whereId($this->parameter->order->id)->first();
            $baseUrl = SiteConfig::fetch("storefront_base_urL", "website", $this->coreCache?->website?->id);
            $data = [
                "merchantAccount" => $config_data->api_merchant_account,
                "amount" => [
                "value" => (float) $order?->grand_total * place_decimal($order->currency_code),
                "currency" => $order->currency_code
                ],
                "returnUrl" => "{$baseUrl}{$this->coreCache->channel?->code}/{$this->coreCache->store?->code}/checkout",  // TODO we need to change the  base url into dynamic url
                "reference" => "hdl|{$this->coreCache->website->hostname}|{$this->coreCache->channel->code}|{$this->coreCache->store->code}|{$order->id}",
                "countryCode" => $config_data->default_country->iso_2_code,
                "expiresAt" => Carbon::now()->addHours(24)->toIso8601String(),  // date format must be in ISO 8601
            ];
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return $data;
    }
}
