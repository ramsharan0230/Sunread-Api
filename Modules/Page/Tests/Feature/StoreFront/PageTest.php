<?php

namespace Modules\Page\Tests\Feature\StoreFront;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Core\Entities\Store;
use Modules\Core\Tests\StoreFrontBaseTestCase;
use Modules\Page\Entities\Page;
use Tests\TestCase;

class PageTest extends StoreFrontBaseTestCase
{
    public function setUp(): void
    {
        $this->model = Page::class;

        parent::setUp();

        $this->model_name = "Page";
        $this->route_prefix = "public.pages";

        $this->createFactories = false;
        $this->hasFilters = false;
        $this->hasIndexTest = false;
        
        $this->createHeader();
    }

    public function createScopeData()
    { 
        $this->default_resource->update(["website_id" => $this->website->id]);
        $this->default_resource->page_scopes()->create([
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