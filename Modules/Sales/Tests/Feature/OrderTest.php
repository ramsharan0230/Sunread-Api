<?php

namespace Modules\Sales\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Sales\Entities\Order;
use Modules\Sales\Entities\OrderStatus;

class OrderTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Order::class;
        parent::setUp();

        $this->admin = $this->createAdmin();

        $this->model_name = "Order";
        $this->route_prefix = "admin.sales.orders";

        $this->createFactories = false;
        $this->hasStoreTest = false;
        $this->hasUpdateTest = false;
        $this->hasDestroyTest = false;
    }

    public function testAdminCanUpdateOrderStatus()
    {
        $order_status_id = OrderStatus::first()->id;
        $order_id = Order::first()->id;
        $post_data = [ "order_status_id" => $order_status_id, "order_id" => $order_id];
        $response = $this->withHeaders($this->headers)->post(route("admin.sales.order.status"), $post_data);
        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }
}
