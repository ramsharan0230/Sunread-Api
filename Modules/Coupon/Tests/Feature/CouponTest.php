<?php

namespace Modules\Coupon\Tests\Feature;

use Illuminate\Support\Arr;
use Modules\Core\Tests\BaseTestCase;
use Modules\Coupon\Entities\AllowCoupon;
use Modules\Coupon\Entities\Coupon;

class CouponTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Coupon::class;

        parent::setUp();

        $this->admin = $this->createAdmin();
        $this->model_name = "Coupon";
        $this->route_prefix = "admin.coupons";
        $this->hasStatusTest = true;
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "name" => null,
            "status"=>null
        ]);
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "discount_percent" => null
        ]);
    }

    public function testAdminCanFetchModelListResources()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("model_list"));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name"=>"Model List"])
        ]);
    }
}
