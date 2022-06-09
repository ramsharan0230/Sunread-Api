<?php

namespace Modules\Country\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Country\Entities\Region;

class RegionTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Region::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Region";
        $this->route_prefix = "admin.regions";

        $this->createFactories = false;
        $this->hasStoreTest = false;
        $this->hasUpdateTest = false;
        $this->hasDestroyTest = false;
    }

    public function testAdminCanFetchCountryWiseRegions()
    {
        $post_data = [
            "country_id" => 1
        ];
        $response = $this->withHeaders($this->headers)->get(route("admin.country.regions.list", $post_data));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }
}
