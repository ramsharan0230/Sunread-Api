<?php

namespace Modules\Erp\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Erp\Entities\NavErpOrderMapper;
use Modules\Core\Entities\Website;
use Illuminate\Support\Arr;
use Modules\Country\Entities\Country;

class ErpMapperTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = NavErpOrderMapper::class;
        parent::setUp();

        $this->admin = $this->createAdmin();

        $this->model_name = "Nav Erp Order Mapper";
        $this->route_prefix = "admin.erp.mappers";
        $this->hasIndexTest = false;
    }

    public function getCreateData(): array
    {
        $countries = NavErpOrderMapper::pluck('country_id');
        $country = Country::whereNotIn('id', $countries)
            ->inRandomOrder()
            ->first();

        return array_merge($this->model::factory()->make()->toArray(), [
            "country_id" => $country->id
        ]);
    }

    public function testAdminCanFetchResources()
    {
        if ($this->createFactories) {
            $this->model::factory($this->factory_count)->create();
        }

        $websiteId = Website::inRandomOrder()->first()->id;
        $response = $this->withHeaders($this->headers)->get($this->getRoute("index", ['website_id'=>$websiteId]));
        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "website_id" => null
        ]);
    }

    public function getNonMandatoryUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "country" => null
        ]);
    }
}
