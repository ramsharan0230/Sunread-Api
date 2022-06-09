<?php

namespace Modules\PaymentAdyen\Tests\Feature;

use Illuminate\Support\Arr;
use Modules\Sales\Entities\Order;
use Modules\Core\Tests\StoreFrontBaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdyenOrderStatusTest extends StoreFrontBaseTestCase
{
    public function setUp(): void
    {
        $this->model = Order::class;

        parent::setUp();

        $this->model_name = "Order";
        $this->route_prefix = "adyen";

        $this->createFactories = false;
        $this->hasFilters = false;
        $this->hasIndexTest = false;
        $this->hasShowTest = false;

        $this->createHeader();
    }

    public function getCreateData(): array
    {
        return [
            "order_id" => $this->default_resource->id,
            "result_code" => $this->getRandomCode("Authorised"),
        ];
    }

    public function getInvalidCreateData(): array
    {
        return [ "order_id" => null, ];
    }

    private function getRandomCode($result_code = null): string
    {
        return $result_code ?? Arr::random(["Received", "Authorised", "Refused", "Expired", "Cancelled", "Error"]);
    }

    public function testShouldReturnErrorIfGetDataIsInvalid(): void
    {
        $response = $this->withHeaders($this->getHeaders())->post($this->getRoute("update.order.status"), $this->getInvalidCreateData());
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testShouldUpdateOrderStatus(): void
    {
        $data = $this->getCreateData();
        $response = $this->withHeaders($this->getHeaders())->post($this->getRoute("update.order.status"), $data);
        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.order-status-updated")
        ]);
    }

    private function getHeaders(): array
    {
        $this->headers = array_merge($this->headers, [
            "hc-host" => "international.co",
            "hc-channel" => "international",
            "hc-store" => "international-store"
        ]);

        return $this->headers;
    }
}
