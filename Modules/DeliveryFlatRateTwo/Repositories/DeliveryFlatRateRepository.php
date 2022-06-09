<?php

namespace Modules\DeliveryFlatRateTwo\Repositories;

use Exception;
use Modules\CheckOutMethods\Contracts\DeliveryMethodInterface;
use Modules\DeliveryFlatRate\Traits\DeliveryFlatRateCalculation;
use Modules\CheckOutMethods\Repositories\BaseDeliveryMethodRepository;
use Modules\Sales\Repositories\OrderTaxItemRepositroy;
use Modules\Sales\Repositories\OrderTaxRepositroy;
use Modules\Sales\Repositories\StoreFront\OrderRepository;

class DeliveryFlatRateRepository extends BaseDeliveryMethodRepository implements DeliveryMethodInterface
{
    use DeliveryFlatRateCalculation;

    protected object $request;
    protected string $method_key;
    protected object $parameter;

    public $orderTaxItemRepostory;
    public $orderTaxRepository;
    public $orderRepository;
    public $orderMetaRepository;

    public function __construct(object $request, object $parameter)
    {
        $this->request = $request;
        $this->parameter = $parameter;
        $this->method_key = "flat_rate_two";

        $this->orderTaxItemRepostory = new OrderTaxItemRepositroy();
        $this->orderTaxRepository = new OrderTaxRepositroy();
        $this->orderRepository = new OrderRepository();

        parent::__construct($this->request, $this->method_key);
    }

    public function get(): mixed
    {
        try
        {
            $coreCache = $this->getCoreCache();
            $order = $this->orderModel->whereId($this->parameter->order->id)->first();
            $arr_shipping = $this->getDeliveryFlatRateCalculate($coreCache, $order);
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return $arr_shipping;
    }

}