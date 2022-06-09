<?php

namespace Modules\CheckOutMethods\Repositories;

use Exception;
use Modules\Sales\Entities\Order;
use Modules\Core\Facades\CoreCache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Modules\CheckOutMethods\Services\MethodAttribute;
use Modules\CheckOutMethods\Traits\HasBasePaymentMethod;
use Modules\CheckOutMethods\Traits\HasCheckOutHttpClient;

class BasePaymentMethodRepository
{
    use HasBasePaymentMethod;
    use HasCheckOutHttpClient;

    protected $payment_data, $encryptor;
    protected object $request;
    protected string $method_key;
    protected object $parameter;
    protected array $rules;

    public object $coreCache;
    public array $method_detail;
    public string $base_url;
    public array $headers;
    public string $user_name, $password;
    public $orderRepository;
    public $orderMetaRepository;
    public $order;
    public mixed $base_data;
    public object $orderModel;
    public array $relations;

    public function __construct(
        object $request,
        string $method_key,
        object $parameter,
        ?array $rules = []
    ) {
        $this->request = $request;
        $this->method_key = $method_key;
        $this->parameter = $parameter;
        $this->rules = $rules;
        $this->coreCache =  $this->getCoreCache();
        $this->method_detail = [
            "method_key" => $method_key
        ];
        $this->headers = [ "Accept" => "application/json" ];
        $this->orderMetaRepository = new CheckOutOrderMetaRepository();
        $this->orderRepository = new CheckOutOrderRepository();
        $this->relations = [
            "order_items.order",
            "order_taxes.order_tax_items",
            "website",
            "billing_address", 
            "shipping_address",
            "customer",
            "order_status.order_status_state",
            "order_addresses.city",
            "order_addresses.region",
            "order_addresses.country",
            "order_metas",
            "order_tax_items"
        ];
        $this->orderModel = $this->getModel();
    }

    public function getModel(): object
    {
        return Order::query()->with($this->relations);
    }

    public function object(array $attributes = []): mixed
    {
        return new MethodAttribute($attributes);
    }

    public function collection(array $attributes = []): Collection
    {
        return new Collection($attributes);
    }

    public function getCoreCache(): object
    {
        try
        {
            $data = [];
            if($this->request->header("hc-host")) $data["website"] = CoreCache::getWebsite($this->request->header("hc-host"));
            if($this->request->header("hc-channel")) $data["channel"] = CoreCache::getChannel($data["website"], $this->request->header("hc-channel"));
            if($this->request->header("hc-store")) $data["store"] = CoreCache::getStore($data["website"], $data["channel"], $this->request->header("hc-store"));
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return $this->object($data);
    }

    public function methodDetail(): object
    {
        if (array_key_exists("user_name", $this->method_detail) && array_key_exists("password", $this->method_detail)) {
            $this->user_name = $this->method_detail["user_name"];
            $this->password = $this->method_detail["password"];
        } 
        $this->base_data = $this->object($this->method_detail);
        return $this->base_data;
    }


    public function responseData(mixed $response): mixed
    {
        return $this->object(["response_data" => $response->json(), "response_status" => $response->status() ]);
    }
    
    public function rules(array $merge = []): array
    {
        return array_merge($this->rules, $merge);
    }

    public function validateData(object $request, array $merge = [], ?callable $callback = null): array
    {
        $data = $request->validate($this->rules($merge));

        $append_data = $callback ? $callback($request) : [];
        return array_merge($data, $append_data);
    }

    public function customValidate(array $data, array $rules, ?array $message = []): array
    {
        $validator = Validator::make($data, $rules, $message);
        if ( $validator->fails() ) throw ValidationException::withMessages($validator->errors()->toArray());
        return $validator->validated();
    }
}
