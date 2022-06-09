<?php

namespace Modules\Country\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Country\Entities\City;

class CityTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = City::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "City";
        $this->route_prefix = "admin.cities";

        $this->createFactories = false;
        $this->hasStoreTest = false;
        $this->hasUpdateTest = false;
        $this->hasDestroyTest = false;
    }

    public function testAdminCanFetchRegionWiseCities()
    {
        $post_data = [
            "region_id" => 1
        ];
        $response = $this->withHeaders($this->headers)->get(route("admin.region.cities.list", $post_data));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }
}
