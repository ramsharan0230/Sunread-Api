<?php

namespace Modules\CheckOutMethods\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\CheckOutMethods\Services\MethodAttribute;
use Modules\Core\Facades\CoreCache;
use Modules\Sales\Entities\Order;

class BaseDeliveryMethodRepository
{
    protected object $request;
    protected string $method_key;
    protected string $user_name, $password;
    protected array $rules;
    protected object $coreCache;
    public $orderRepository;
    public $orderMetaRepository;

    public function __construct(object $request, string $method_key, ?array $rules = [])
    {
        $this->request = $request;
        $this->method_key = $method_key;
        $this->rules = $rules;
        $this->coreCache =  $this->getCoreCache();
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
            "order_metas"
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

    public function basicAuth(string $user_name, string $password): object
    {
        return Http::withBasicAuth($user_name, $password);
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
