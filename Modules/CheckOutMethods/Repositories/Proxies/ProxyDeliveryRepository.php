<?php

namespace Modules\CheckOutMethods\Repositories\Proxies;

use Exception;
use Modules\CheckOutMethods\Contracts\DeliveryMethodInterface;
use Modules\CheckOutMethods\Repositories\BaseDeliveryMethodRepository;
use Modules\Core\Facades\SiteConfig;

class ProxyDeliveryRepository extends BaseDeliveryMethodRepository implements DeliveryMethodInterface
{
    protected object $request;
    protected string $method_key;
    protected object $parameter;

    public function __construct(object $request, object $parameter)
    {
        $this->request = $request;
        $this->parameter = $parameter;
        $this->method_key = "proxy_checkout_method";
        parent::__construct($this->request, $this->method_key);
    }

    public function get(): mixed
    {
        try
        {
            $coreCache = $this->getCoreCache();
            $channel_id = $coreCache?->channel->id;
            $arr_shipping = [ "shipping_amount" => 0.00, "shipping_tax" => false ];

            $this->orderRepository->update([
                "shipping_method" => $this->method_key,
                "shipping_method_label" => $this->method_key
            ], $this->parameter->order->id, function ($order) use ($arr_shipping) {
                $this->orderMetaRepository->create([
                    "order_id" => $order->id,
                    "meta_key" => "shipping",
                    "meta_value" => [
                        "shipping_method" => "proxy_shipping_method",
                        "shipping_method_label" => "proxy_shipping_method",
                        "initiated_by" => $this->request->payment_method,
                        "taxes" => $arr_shipping
                    ]
                ]);
            });
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return $arr_shipping;
    }

}
