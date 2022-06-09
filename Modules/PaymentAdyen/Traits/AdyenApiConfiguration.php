<?php

namespace Modules\PaymentAdyen\Traits;

use Exception;
use Illuminate\Support\Facades\Http;
use Modules\Core\Facades\SiteConfig;
use Illuminate\Database\Eloquent\Collection;
use Modules\CheckOutMethods\Services\MethodAttribute;
use Modules\CheckOutMethods\Exceptions\MethodException;

trait AdyenApiConfiguration
{
    public mixed $urls;
    public object $coreCache;
    public array $headers;
    public string $base_url;
    public object $baseData;

    public function collection(array $attributes = []): Collection
    {
        return new Collection($attributes);
    }

    public function apiUrlFormat(): array
    {
        return [
            [
                "type" => "production",
                "url" => "https://checkout-test.adyen.com/checkout/"   // TODO:: take base url of production
            ],
            [
                "type" => "playground",
                "url" => "https://checkout-test.adyen.com/checkout/"
            ]
        ];
    }

    public function getApiUrl(): Collection
    {
        return $this->urls = $this->collection($this->apiUrlFormat());
    }

    public function getBaseUrl(): string
    {
        $data = $this->baseData;
        return $this->urls->where("type", $data->api_mode)->first()["url"];
    }

    public function getBaseData(object $coreCache): object
    {
        try
        {
            $this->coreCache = $coreCache;
            $data = [];
            $paths = [
                "api_mode" => "payment_methods_adyen_api_config_mode",
                "api_merchant_account" => "payment_methods_adyen_api_config_merchant_account",
                "client_key" => "payment_methods_adyen_api_config_client_key",
                "default_country" => "default_country",
                "payment_method_label" => "payment_methods_adyen_title",
                "status" => "payment_methods_adyen_new_order_status",
                "environment" => "payment_methods_adyen_environment",
                "api_key" => "payment_methods_adyen_api_config_api_key"
            ];
            foreach ($paths as $key => $path) $data[$key] = SiteConfig::fetch($path, "channel", $coreCache->channel?->id);
        }
        catch (Exception $exception) {
            throw $exception;
        }

        return $this->object($data);
    }

    public function postClient(string $url, array $data = []): mixed
    {
        try
        {
            $response = Http::withHeaders($this->headers)
            ->post("{$this->base_url}{$url}", $data)
            ->throw()
            ->json();
         }
        catch (Exception $exception )
        {
            throw new MethodException($exception->getMessage(), $exception->getCode());
        }

        return $response;
    }
    
    public function object(array $attributes = []): mixed
    {
        return new MethodAttribute($attributes);
    }
}
