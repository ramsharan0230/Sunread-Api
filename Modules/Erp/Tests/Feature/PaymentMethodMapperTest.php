<?php

namespace Modules\Erp\Tests\Feature;

use Illuminate\Support\Arr;
use Modules\CheckOutMethods\Entities\CheckOutMethod;
use Modules\Core\Tests\BaseTestCase;
use Modules\Erp\Entities\ErpPaymentMethodMapper;
use Modules\Core\Entities\Website;


class PaymentMethodMapperTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = ErpPaymentMethodMapper::class;
        parent::setUp();
        $this->model_name = "Erp Payment Method Mapper";
        $this->admin = $this->createAdmin();

        $this->route_prefix = "admin.erp.mappers.payment";
        $this->hasIndexTest = false;
        $this->hasShouldReturnErrorIfResourceDoesNotExistTest = false;
        $this->websiteId = Website::inRandomOrder()->first()->id;
    }

    public function getCreateData(): array
    {
        return array_merge($this->model::factory()->make()->toArray(), [
            "payment_method" => Arr::random(CheckOutMethod::where("type", CheckOutMethod::PAYMENT_METHOD)->pluck("slug")->toArray()),
        ]);
    }

    public function testAdminCanFetchResources(): void
    {
        if ($this->createFactories) {
            $this->model::factory($this->factory_count)->create();
        }
        $response = $this->withHeaders($this->headers)->get($this->getRoute("index", ['website_id'=>$this->websiteId]));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "website_id" => null,
        ]);
    }
}
