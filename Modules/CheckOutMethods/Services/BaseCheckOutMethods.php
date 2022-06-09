<?php

namespace Modules\CheckOutMethods\Services;

use Exception;
use Illuminate\Support\Collection;
use Modules\CheckOutMethods\Entities\CheckOutMethod;
use Modules\Core\Exceptions\DestructiveMethodException;
use Modules\Core\Facades\SiteConfig;

class BaseCheckOutMethods
{
    protected array $checkout_methods;
    protected array $method_attributes;
    protected mixed $check_out_method;
    protected bool $get_initial_repository;
    public bool $custom_order_process = false;
    public ?object $request;
    public $model;

    public function __construct(?string $check_out_method = null, ?bool $get_initial_repository = false, ?object $request = null)
    {
        $this->model = new CheckOutMethod();
        $this->checkout_methods = $this->model::$checkout_method_types;
        $this->check_out_method = isset($check_out_method) ?  $this->fetch($check_out_method) : $check_out_method;
        $this->get_initial_repository = $get_initial_repository;
        $this->request = $request;
        $this->custom_order_process = $this->can_initilize_order_process();
    }

    public function object(array $attributes = []): mixed
    {
        return new MethodAttribute($attributes);
    }

    public function collection(array $attributes = []): Collection
    {
        return new Collection($attributes);
    }

    public function all(?callable $callback = null): mixed
    {
        try
        {
            $check_out_methods = $this->collection($this->checkout_methods);
            $check_out_methods = $check_out_methods->map( function ($check_out_method) use ($callback) {
                return [
                    $check_out_method => $this->getData($check_out_method, $callback)->unique("slug")->toArray()
                ];
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $check_out_methods;
    }

    public function get(?string $method_name = "delivery_methods", ?callable $callback = null): mixed
    {
        return $this->getData($method_name, $callback);
    }

    public function fetch(string $method_slug, ?callable $callback = null): mixed
    {
        try
        {
            $fetched = $this->all()->flatten(2)->where("slug", $method_slug)->first();
            if ($callback) {
                $fetched = array_merge($fetched, $callback($fetched));
            }
            if (!$fetched) {
                throw new DestructiveMethodException("Could not fetch required attributes.");// exception for dev
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $this->object($fetched);
    }

    public function process(object $request, ?array $parameter = [], ?object $method_data = null): mixed
    {
        try
        {
            if (!empty($parameter)) {
                $parameter = $this->object($parameter);
            }
            if ($this->check_out_method) {
                $data = $this->getRepositoryData($this->check_out_method, $request, $parameter);
            } elseif (isset($method_data)) {
                $data = $this->getRepositoryData($method_data, $request, $parameter);
            } else {
                throw new DestructiveMethodException("Could not process method. Method is not initialize."); // Exception for dev
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    private function getRepositoryData(object $method_data, object $request, ?object $parameter): mixed
    {
        try
        {
            $resolver = new CheckOutProcessResolver($request, $method_data);
            $method_data = $resolver->resolveCheckOutMethod($method_data);
            $method_repository = $method_data->repository;
            if (!class_exists($method_repository)) {
                throw new DestructiveMethodException("Repository Path Not found."); // Exception for dev
            }

            $method_repository = new $method_repository($request, $parameter);
            if ($this->get_initial_repository) {
                return $method_repository;
            }
            $data = $method_repository->get();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    private function getData(string $checkout_method, ?callable $callback = null): mixed
    {
        try
        {
            $proxy_method_data = [ [ "title" => "Proxy Checkout Method", "slug" => "proxy_checkout_method" ] ];
            $data = SiteConfig::get($checkout_method)->merge($proxy_method_data)->map(function ($method) use ($callback, $checkout_method) {
                $data = [
                    "title" => $method["title"],
                    "slug" => $method["slug"],
                    "check_out_method" => $checkout_method,
                    "repository" => array_key_exists("repository", $method) ? $method["repository"] : null,
                ];
                if ($callback) {
                    $data = array_merge($data, $callback($method));
                }
                return $data;
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function can_initilize_order_process(): bool
    {
        try
        {
            $condition = false;
            if ($this->request) {
                $resolver = new CheckOutProcessResolver($this->request);
                $custom_resolver = $resolver->getResolveOrderProcessData();
                $condition = $custom_resolver->resolve;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $condition;
    }

}
