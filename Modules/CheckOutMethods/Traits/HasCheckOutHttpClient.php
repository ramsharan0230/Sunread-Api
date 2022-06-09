<?php

namespace Modules\CheckOutMethods\Traits;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Client\PendingRequest;
use Modules\CheckOutMethods\Exceptions\MethodException;

trait HasCheckOutHttpClient
{
    public function http(): PendingRequest
    {
        return (array_key_exists("user_name", $this->method_detail) && array_key_exists("password", $this->method_detail)) ? Http::withHeaders($this->headers)->withBasicAuth($this->user_name, $this->password) : Http::withHeaders($this->headers);
    }

    public function getClient(string $url, ?array $query = []): mixed
    {
        Event::dispatch("{$this->method_key}.get-basic-auth.before");

        try
        {
            $response = $this->http()
            ->get("{$this->base_url}{$url}", $query)
            ->throw()
            ->json();
        }
        catch (Exception $exception )
        {
            throw new MethodException($exception->getMessage(), $exception->getCode());
        }

        Event::dispatch("{$this->method_key}.get-basic-auth", $response);
        return $response;
    }

    public function postClient(string $url, array $data = []): mixed
    {
        Event::dispatch("{$this->method_key}.post-basic-auth.before");

        try
        {
            $response = $this->http()
            ->post("{$this->base_url}{$url}", $data)
            ->throw()
            ->json();
         }
        catch (Exception $exception )
        {
            throw new MethodException($exception->getMessage(), $exception->getCode());
        }

        Event::dispatch("{$this->method_key}.post-basic-auth", $response);
        return $response;
    }

    public function putClient(string $url, array $data = []): mixed
    {
        Event::dispatch("{$this->method_key}.put-basic-auth.before");

        try
        {
            $response = $this->http()
            ->put("{$this->base_url}{$url}", $data)
            ->throw()
            ->json();
        }
        catch (Exception $exception )
        {
            throw new MethodException($exception->getMessage(), $exception->getCode());
        }

        Event::dispatch("{$this->method_key}.put-basic-auth", $response);
        return $response;
    }

    public function deleteClient(string $url, array $data = []): mixed
    {
        Event::dispatch("{$this->method_key}.put-basic-auth.before");

        try
        {
            $response = $this->http()
            ->delete("{$this->base_url}{$url}", $data)
            ->throw()
            ->json();
        }
        catch (Exception $exception )
        {
            throw new MethodException($exception->getMessage(), $exception->getCode());
        }

        Event::dispatch("{$this->method_key}.put-basic-auth", $response);
        return $response;
    }
}
