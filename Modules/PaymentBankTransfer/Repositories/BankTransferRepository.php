<?php

namespace Modules\PaymentBankTransfer\Repositories;

use Exception;
use Modules\Cart\Repositories\CartRepository;
use Modules\Core\Facades\SiteConfig;
use Modules\Sales\Traits\HasCheckoutUser;
use Modules\Sales\Repositories\StoreFront\OrderRepository;
use Modules\CheckOutMethods\Contracts\PaymentMethodInterface;
use Modules\Sales\Exceptions\BankTransferNotAllowedException;
use Modules\CheckOutMethods\Repositories\BasePaymentMethodRepository;
use Modules\Customer\Repositories\StoreFront\CustomerCheckoutRepository;
use Modules\Sales\Repositories\OrderAddressRepository;

class BankTransferRepository extends BasePaymentMethodRepository implements PaymentMethodInterface
{
    use HasCheckoutUser;

    protected object $request;
    protected object $parameter;
    protected string $method_key;
    public $customerRepository;
    public $orderRepository;
    public $cartRepository;
    public $orderAddressRepository;

    public function __construct(
        object $request,
        object $parameter
    ) {
        $this->request = $request;
        $this->method_key = "bank_transfer";
        $this->parameter = $parameter;
        $this->customerRepository = new CustomerCheckoutRepository();
        $this->orderRepository = new OrderRepository();
        $this->cartRepository = new CartRepository();
        $this->orderAddressRepository =  new OrderAddressRepository();
        
        parent::__construct($this->request, $this->method_key, $this->parameter);
    }

    public function get(): mixed
    {
        try 
        {
            $coreCache = $this->getCoreCache();
            $channel_id = $coreCache?->channel->id;
            $minimum_order_total = SiteConfig::fetch("payment_methods_{$this->method_key}_minimum_total_order", "channel", $channel_id);
            $maximum_order_total = SiteConfig::fetch("payment_methods_{$this->method_key}_maximum_total_order", "channel", $channel_id);
            if (($this->parameter->order->sub_total_tax_amount < $minimum_order_total) || ($this->parameter->order->sub_total_tax_amount > $maximum_order_total)) {
                throw new BankTransferNotAllowedException(__("core::app.sales.payment-transfer-not-allowed", ["minimum_order_total" => $minimum_order_total, "maximum_order_total" => $maximum_order_total]), 403);
            }
            
            $payment_method_data = [
                "payment_method" => $this->method_key,
                "payment_method_label" => SiteConfig::fetch("payment_methods_{$this->method_key}_title", "channel", $channel_id),
                "minimum_order_total" => $minimum_order_total,
                "maximum_order_total" => $maximum_order_total,
                "status" => SiteConfig::fetch("payment_methods_{$this->method_key}_new_order_status", "channel", $channel_id)?->slug
            ];
            $order_data = $payment_method_data;
            unset($order_data["minimum_order_total"], $order_data["maximum_order_total"]);

            $this->orderRepository->update($order_data, $this->parameter->order->id, function ($order) use ($payment_method_data) {
                $match = [
                    "order_id" => $order->id,
                    "meta_key" => "{$this->method_key}_response",
                ];
                $data = array_merge($match, ["meta_value" => $payment_method_data]);
                $this->orderMetaRepository->createOrUpdate($match, $data);
            });

            $this->orderAddressRepository->store($this->request, $this->parameter->order);

            if ($this->request->create_account) {
                $this->createGuestUser($this->parameter->order);
            }

            $this->cartRepository->delete($this->parameter->order->cart_id);
        }
        catch ( Exception $exception )
        {
            throw $exception;
        }

        return true;
    }
}
