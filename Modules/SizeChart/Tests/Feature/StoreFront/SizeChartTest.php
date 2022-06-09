<?php

namespace Modules\SizeChart\Tests\Feature\StoreFront;

use Modules\Core\Tests\StoreFrontBaseTestCase;
use Modules\SizeChart\Entities\SizeChart;

class SizeChartTest extends StoreFrontBaseTestCase
{
    public function setUp(): void
    {
        $this->model = SizeChart::class;

        parent::setUp();

        $this->model_name = "Size Chart";
        $this->route_prefix = "public.sizecharts";

        $this->createFactories = false;
        $this->hasFilters = false;
        $this->hasIndexTest = false;
        $this->createHeader();
    }

    public function createScopeData()
    {
        $this->default_resource->update(["website_id" => $this->website->id]);
        $this->default_resource->size_chart_scopes()->create([
            "scope" => "store",
            "scope_id" => $this->store->id
        ]);
    }

    public function testAdminCanFetchIndividualResource()
    {
        $this->createScopeData();
        $response = $this->withHeaders($this->headers)->get($this->getRoute("show", [$this->default_resource_slug]));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }
}