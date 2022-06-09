<?php

namespace Modules\CheckOutMethods\Repositories\Proxies;

use Modules\CheckOutMethods\Contracts\PaymentMethodInterface;
use Modules\CheckOutMethods\Repositories\BaseDeliveryMethodRepository;

class ProxyPaymentRepository extends BaseDeliveryMethodRepository implements PaymentMethodInterface
{
    protected object $request;
    protected object $parameter;
    protected string $method_key;

    public function __construct(object $request, object $parameter)
    {
        $this->request = $request;
        $this->method_key = "proxy_checkout_method";
        $this->parameter = $parameter;
        
        parent::__construct($this->request, $this->method_key);
    }

    public function get(): mixed
    {
        return true;
    }

}