<?php

namespace Modules\Coupon\Tests\Feature;

use Illuminate\Support\Arr;
use Modules\Core\Tests\BaseTestCase;
use Modules\Coupon\Entities\AllowCoupon;
use Modules\Coupon\Entities\Coupon;

class AllowCouponTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Coupon::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Allow Coupon";
        $this->route_prefix = "admin.coupons";

        $this->hasFilters = false;
        $this->hasIndexTest = false;
        $this->hasShowTest = false;
        $this->hasStoreTest = false;
        $this->hasUpdateTest = false;
        $this->hasDestroyTest = false;
        $this->hasBulkDestroyTest = false;
        $this->hasStatusTest = false;
    }

    public function testAdminCanCreateResourceWithNonMandatoryData()
    {
        $this->markTestSkipped("All data are mandatory.");
    }

    public function testAdminCanAllowCoupon()
    {
        $model_type = Arr::random(config('model_list.model_types'));
        $resource_ids = app($model_type)::factory(2)->create()->pluck("id")->toArray();
        $post_data = [
            [
                "model_type" => $model_type,
                "model_id" => $resource_ids,
                "status" => 1
            ]
        ];

        $response = $this->withHeaders($this->headers)->post($this->getRoute("allow_coupon", [$this->default_resource_id]), $post_data);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.create-success", ["name" => "Allow Coupon"])
        ]);
    }

    public function testAdminCanDeleteAllowCoupon()
    {
        $resource_ids = AllowCoupon::factory(2)->create()->pluck("id")->toArray();

        $response = $this->withHeaders($this->headers)->delete($this->getRoute("delete_allow_coupon"),[
            "ids" => $resource_ids
        ]);

        $response->assertOk();

        $check_resource = AllowCoupon::whereIn("id", $resource_ids)->get()->count() > 0 ? true : false;
        $this->assertFalse($check_resource);
    }
}
