<?php

namespace Modules\CheckOutMethods\Entities;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Facades\SiteConfig;

class CheckOutMethod extends Model
{
    const PAYMENT_METHOD = "payment_methods";
    const DELIVERY_METHOD = "delivery_methods";

    const PROXY_PAYMENT_METHOD = "proxy_payment_methods";
    const PROXY_DELIVERY_METHOD = "proxy_delivery_methods";

    const PROXY_PAYMENT_METHOD_REPOSITORY_PATH = "Modules\CheckOutMethods\Repositories\Proxies\ProxyPaymentRepository";
    const PROXY_DELIVERY_METHOD_REPOSITORY_PATH = "Modules\CheckOutMethods\Repositories\Proxies\ProxyDeliveryRepository";


    public static $checkout_method_types = [
        self::PAYMENT_METHOD,
        self::DELIVERY_METHOD,
    ];

    public static $proxy_methods = [
        self::PROXY_DELIVERY_METHOD,
        self::PROXY_PAYMENT_METHOD,
    ];

    public static $proxy_method_repository_paths = [
        self::PROXY_PAYMENT_METHOD_REPOSITORY_PATH,
        self::PROXY_DELIVERY_METHOD_REPOSITORY_PATH,
    ];

    protected $guarded = [];

    public function __construct(?array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function checkoutMethods(?callable $callback = null): Collection
    {
        try
        {
            $methods = [];
            foreach (self::$checkout_method_types as $type) {
                $method = SiteConfig::get($type);
                $method_lists = $method->pluck("slug")->unique()->values()->toArray();
                $methods = array_merge($methods,
                    array_map(function ($method) use ($type, $callback) {
                        $method = [
                            "id" => md5($method),
                            "name" => ucwords($method),
                            "slug" => $method,
                            "type" => $type,
                        ];
                        $method = $callback ? $callback($method) : $method;
                        return new self($method);
                }, $method_lists));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return new Collection($methods);
    }

    public static function get(): Collection
    {
        return self::checkoutMethods();
    }

    public static function where(string $key, string $value): Collection
    {
        return self::checkoutMethods()->where($key, $value);
    }
}
