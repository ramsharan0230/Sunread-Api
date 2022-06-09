<?php

namespace Modules\Erp\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Erp\Entities\ErpShippingAttributeMapper;
use Modules\Core\Entities\Website;


class ShippingAttributeMapperTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = ErpShippingAttributeMapper::class;
        parent::setUp();

        $this->admin = $this->createAdmin();

        $this->model_name = "Erp Shipping Attribute Mapper";
        $this->route_prefix = "admin.erp.mappers.attributes";
        $this->hasIndexTest = false;
        $this->hasShouldReturnErrorIfResourceDoesNotExistTest = false;
        $this->websiteId = Website::inRandomOrder()->first()->id;
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
