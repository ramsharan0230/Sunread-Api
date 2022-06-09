<?php

namespace Modules\CheckOutMethods\Services;

use Exception;
use Illuminate\Support\Arr;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Facades\SiteConfig;
use Modules\Sales\Transformers\OrderResource;
use Modules\CheckOutMethods\Entities\CheckOutMethod;
use Modules\Core\Services\Pipe;
use Modules\Sales\Repositories\StoreFront\OrderRepository;
use Modules\Sales\Exceptions\PaymentMethodDisabledException;

class CheckOutProcessResolver
{
    protected object $request;
    protected ?object $method_data;
    public array $custom_checkout_handler;
    protected $model;
    public $orderRepository;

    public function __construct(object $request, ?object $method_data = null)
    {
        $this->request = $request;
        $this->method_data = $method_data;
        $this->model = new CheckOutMethod();
        $this->orderRepository = new OrderRepository();
        $this->custom_checkout_handler = [
            //key should be same as slug
            "klarna" => [
                "slug" => "klarna",
                "method" => CheckOutMethod::PAYMENT_METHOD,
                "handles" => CheckOutMethod::$checkout_method_types,
                // "configuration_path" => "payment_methods_klarna_api_config_allow_custom_shipping_handler",
                "manage_order" => true,
                "resource" => new OrderRepository(array()),
                "repository" => new OrderRepository(),
                "priority" => 0, // top priority
            ],
            "adyen" => [
                "slug" => "adyen",
                "method" => CheckOutMethod::PAYMENT_METHOD,
                "handles" => [CheckOutMethod::PAYMENT_METHOD],
                "resource" => new OrderRepository(array()),
                "repository" => new OrderRepository(),
                "priority" => 1,
            ],
        ];
    }

    public function object(array $attributes = []): mixed
    {
        return new MethodAttribute($attributes);
    }

    public function check(string $value, string $checkout_method): bool
    {
        try
        {
            $condition = false;
            switch ($checkout_method) {
                case "delivery_methods":
                    $request_payment_method = $this->request->payment_method;
                    $condition = $this->is_custom_logic_implemented($request_payment_method, $condition, callback: function ($method_data, $condition) use ($value) {
                        return ($condition) ? (in_array("delivery_methods", $method_data["handles"]) && ($value == "proxy_checkout_method")) : $condition;
                    });
                break;

                case "payment_methods":
                    $request_delivery_method = $this->request->shipping_method;
                    $condition = $this->is_custom_logic_implemented($request_delivery_method, $condition, callback: function ($method_data, $condition) use ($value) {
                        return ($condition) ? (in_array("payment_methods", $method_data["handles"]) && ($value == "proxy_checkout_method")) : $condition;
                    });
                break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $condition;
    }

    public function can_initilize(string $checkout_method): bool
    {
        try
        {
            $condition = false;
            switch ($checkout_method) {
                case "delivery_methods":
                    $request_payment_method = $this->request->payment_method;
                    $condition = $this->is_custom_logic_implemented($request_payment_method, $condition, callback: function($method_data, $condition) {
                        return ($condition) ? (in_array("delivery_methods", $method_data["handles"])) : $condition;
                    });
                break;

                case "payment_methods":
                    $request_delivery_method = $this->request->shipping_method;
                    $condition = $this->is_custom_logic_implemented($request_delivery_method, $condition, callback: function($method_data, $condition) {
                        return ($condition) ? (in_array("payment_methods", $method_data["handles"])) : $condition;
                    });
                break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $condition;
    }

    private function is_custom_logic_implemented(?string $method = null, bool $condition, mixed $method_data = null, ?callable $callback = null): bool
    {
        try
        {
            $get_method_data = Arr::get($this->custom_checkout_handler, $method);
            if ($method_data) {
                $get_method_data = $method_data;
            }
            if (!is_null($get_method_data)) {
                if ($callback) {
                    $condition = $callback($get_method_data, $condition);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $condition;
    }

    public function allow_custom_checkout(string $checkout_method): bool
    {
        try
        {
            $condition = false;
            switch ($checkout_method) {
                case "delivery_methods":
                    $method = collect(Arr::get($this->getCheckOutMethods(), "payment_methods"))->where("custom_logic", true)->first();
                    if ($method) {
                        $condition = $this->is_custom_logic_implemented($method["slug"], $condition);
                    }
                break;

                case "payment_methods":
                    $method = collect(Arr::get($this->getCheckOutMethods(), "delivery_methods"))->where("custom_logic", true)->first();
                    if ($method) {
                        $condition = $this->is_custom_logic_implemented($method["slug"], $condition);
                    }
                break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $condition;
    }

    private function is_custom_checkout_enabled(string $configuration_path): bool
    {
        try
        {
            $coreCache = $this->getCoreCache();
            $allow_custom_shipping_handler = SiteConfig::fetch($configuration_path, "channel", $coreCache->channel->id)?->pluck("iso_2_code")?->toArray();
            $channel_country = SiteConfig::fetch("default_country", "channel", $coreCache->channel->id)?->iso_2_code;
            $condition = in_array($channel_country, $allow_custom_shipping_handler);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $condition;
    }

    public function getCheckOutMethods(?bool $filter_list = true, ?callable $callback = null): mixed
    {
        try
        {
            $coreCache = $this->getCoreCache();
            $method_lists = [];

            foreach ($this->model::$checkout_method_types as $check_out_method ) {
                $get_method_list = $this->model::where("type", $check_out_method);
                foreach ($get_method_list as $key => $method) {
                    $value = SiteConfig::fetch("{$check_out_method}_{$method->slug}", "channel", $coreCache->channel?->id);
                    if ($filter_list && !$value) {
                        continue;
                    }
                    $title = SiteConfig::fetch("{$check_out_method}_{$method->slug}_title", "channel", $coreCache->channel?->id);
                    $method_lists[$check_out_method][$key] = [
                        "slug" => $method->slug,
                        "title" => $title,
                        "custom_logic" => in_array($method->slug, array_keys($this->custom_checkout_handler)),
                        "handles" => $this->getHandlerMethods($method->slug, $check_out_method),
                        "visible" => true,
                    ];
                }
                if ($callback) {
                    $method_lists[$check_out_method] = $callback(array_values($method_lists[$check_out_method]), $check_out_method);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $method_lists;
    }

    private function getHandlerMethods(string $slug, string $check_out_method): array
    {
        if (in_array($slug, array_keys($this->custom_checkout_handler))) {
            return $this->custom_checkout_handler[$slug]["handles"];
        }
        return [$check_out_method];
    }

    public function resolveCheckOutMethod(mixed $check_out_method): mixed
    {
        try
        {
            if ($this->can_initilize($check_out_method->check_out_method)) {
                switch ($check_out_method->check_out_method) {
                    case "delivery_methods":
                        $check_out_method["repository"] = $this->model::PROXY_DELIVERY_METHOD_REPOSITORY_PATH;
                    break;

                    case "payment_methods":
                        $check_out_method["repository"] = $this->model::PROXY_PAYMENT_METHOD_REPOSITORY_PATH;
                    break;
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $check_out_method;
    }

    public function getResolveOrderProcessData(): object
    {
        try
        {
            $coreCache = $this->getCoreCache();
            //TODO::dynamically handle checkout methods for future.
            $get_method_list = $this->model::where("type", $this->model::PAYMENT_METHOD)
                ->pluck("slug")
                ?->toArray();

            $taken = new Pipe(collect($this->custom_checkout_handler));
            $check_out_method = $taken->pipe($taken->value->whereIn("slug", $get_method_list))
                ->pipe($this->getFilterCheckoutMethod($taken->value, $coreCache))
                ->pipe($taken->value->first())
                ->value;
            $data = $check_out_method
                ? array_merge($check_out_method, [
                    "resolve" => (bool) ($check_out_method),
                ]) : [
                    "resolve" => false,
                    "resource" => new OrderResource(array()),
                    "repository" => new OrderRepository(),
                ];

        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $this->object($data);
    }


    private function getFilterCheckoutMethod(object $check_out_method, object $coreCache): object
    {
        try
        {
            $data = $check_out_method->filter(function ($check_out_method) use ($coreCache) {
                return (bool) (SiteConfig::fetch("{$check_out_method['method']}_{$check_out_method['slug']}", "channel", $coreCache->channel?->id));
            });
        }   
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getCoreCache(): object
    {
        try
        {
            $data = [];
            if ($this->request->header("hc-host")) {
                $data["website"] = CoreCache::getWebsite($this->request->header("hc-host"));
            }
            if ($this->request->header("hc-channel")) {
                $data["channel"] = CoreCache::getChannel($data["website"], $this->request->header("hc-channel"));
            }
            if ($this->request->header("hc-store")) {
                $data["store"] = CoreCache::getStore($data["website"], $data["channel"], $this->request->header("hc-store"));
            }
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return $this->object($data);
    }

    public function getTopPriorityPaymentMethod(): string
    {
        try
        {
            $payment_methods = Arr::get($this->getCheckOutMethods(), $this->model::PAYMENT_METHOD);
            $taken = new Pipe(collect($this->custom_checkout_handler));
            $check_out_method = $taken->pipe($taken->value->sortBy("priority"))
                ->pipe($this->getFilteredCheckoutMethod($taken->value, $payment_methods))
                ->pipe($taken->value->first())
                ->value;

            if (!$check_out_method) {
                throw new PaymentMethodDisabledException(__("core::app.response.payment-method-not-enabled", ["name" => "Payment Method"])); // this exception means one payment method of $this->custom_checkout_handler array must be enabled
            }

            $data = $check_out_method["slug"];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    private function getFilteredCheckoutMethod(object $custom_checkout_handler, ?array $payment_methods): ?object
    {
        try
        {
            $data = $custom_checkout_handler->filter(function ($handler) use ($payment_methods) {
                return (bool) (!empty($payment_methods) && in_array($handler["slug"], array_column($payment_methods, "slug")));
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function enableDeliveryValidation(object $request): bool
    {
        try
        {
            $step = $request->step ?? 0;
            $request->merge(["step" => $step]);
            $request->validate(["step" => "required|int|in:0,1"]);
            $enable_delivery_validation = false;
            $exist_in_handlers = in_array($request->payment_method, array_keys($this->custom_checkout_handler));
            $delivery_method_exist = in_array($this->model::DELIVERY_METHOD, $this->custom_checkout_handler[$request->payment_method]["handles"]);
            if ($step == 1 && $exist_in_handlers && !$delivery_method_exist) {
                $enable_delivery_validation = true;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $enable_delivery_validation;
    }
}