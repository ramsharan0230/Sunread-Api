<?php

namespace Modules\Sales\Traits;

use Exception;
use Illuminate\Support\Str;
use Modules\Customer\Repositories\StoreFront\CustomerCheckoutRepository;

trait HasCheckoutUser
{
    public function createGuestUser(object $order): void
    {
        try
        {
            $order = $this->orderRepository->fetch($order->id);
            $customer = (new CustomerCheckoutRepository())->queryFetch(["email" => $order->customer_email]);
            if ($order->is_guest && !$customer) {
                $password = Str::random(8);
                $data = [
                    "first_name" => $order->customer_first_name,
                    "middle_name" => $order->customer_middle_name,
                    "last_name" => $order->customer_last_name,
                    "email" => $order->customer_email,
                    "store_id" => $order->store_id,
                    "website_id" => $order->website_id,
                    "phone" => $order->customer_phone,
                    "password" => $password,
                    "password_confirmation" => $password,
                ];
                $customer = (new CustomerCheckoutRepository())->registration($data);
                $this->orderRepository->update([
                    "customer_id" => $customer->id,
                    "is_guest" => 0,
                ], $order->id);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }
}