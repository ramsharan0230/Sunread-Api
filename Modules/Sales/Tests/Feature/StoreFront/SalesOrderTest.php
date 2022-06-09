<?php

namespace Modules\Sales\Tests\Feature\StoreFront;

use Modules\Cart\Entities\Cart;
use Modules\Core\Tests\BaseTestCase;
use Modules\Core\Tests\StoreFrontBaseTestCase;
use Modules\Sales\Entities\Order;
use Modules\Sales\Entities\OrderAddress;

class SalesOrderTest extends StoreFrontBaseTestCase
{
    public function setUp(): void
    {
        $this->model = Order::class;

        parent::setUp();

        $this->model_name = "Order";
        $this->route_prefix = "public.sales.orders";

        $this->createFactories = false;
        $this->hasFilters = false;
        $this->hasIndexTest = false;
        $this->hasShowTest = false;
        $this->hasChannel = false;

        $this->createHeader();
    }

    // public function testUserCanCreateOrder()
    // {
    //     $data = [
    //         "cart_hash_id" => Cart::first()->id,
    //         "customer" => [
    //             "first_name" => $this->faker->firstName(),
    //             "last_name" => $this->faker->lastName(),
    //             "phone" => $this->faker->phone,
    //             "email" => $this->faker->email,
    //         ],
    //         "address" => OrderAddress::factory()->make([
    //             "address_type" => "billing",
    //         ])
    //     ];
    // }



}
