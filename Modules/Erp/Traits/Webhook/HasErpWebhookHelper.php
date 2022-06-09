<?php

namespace Modules\Erp\Traits\Webhook;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

trait HasErpWebhookHelper
{
    private function httpRequest(): PendingRequest
    {
        try
        {
            $request = Http::withBasicAuth(config("erp_config.user_name"), config("erp_config.password"));
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $request;
    }

    public function httpGet(string $url, ?array $query = null): Response
    {
        try
        {
            $response = $this->httpRequest()->get("{$this->base_url}{$url}", $query);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $response;
    }
}
