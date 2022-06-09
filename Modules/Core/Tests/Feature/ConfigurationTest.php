<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Entities\Configuration;
use Modules\Core\Tests\BaseTestCase;

class ConfigurationTest extends BaseTestCase
{
    public $default_resource;

    public function setUp(): void
    {
        $this->model = Configuration::class;

        parent::setUp();

        $this->admin = $this->createAdmin();

        $this->model_name = "Configuration";
        $this->route_prefix = "admin.configurations";

        $this->hasFilters = false;
        $this->hasShowTest = false;
        $this->hasUpdateTest = false;
        $this->hasDestroyTest = false;

        $this->default_resource = Configuration::latest()->first();
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "scope" => "invalid",
            "scope_id" => 1,
            "items" => [
                "optional_zip_countries" => [
                    "value" => 5,
                    "absolute_path" => "general.children.0.subChildren.0.elements.2"
                ]
            ]
        ]);
    }

    /**
     * Fetch tests
     */

    public function testAdminCanFetchResources()
    {
        $response = $this->withHeaders($this->headers)->get(route("{$this->route_prefix}.index"));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    /**
     * POST tests
     *
     * 1. Assert if application returns correct error if scope is invalid
     * 2. Assert if application returns correct error if scope_id is invalid
     */

    public function testShouldReturnErrorIfScopeFieldIsInvalid()
    {
        $post_data = array_merge($this->model::factory()->make()->toArray(), [
            "scope" => "invalid"
        ]);
        $response = $this->withHeaders($this->headers)->post(route("{$this->route_prefix}.store"), $post_data);
        $response->assertStatus(422);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testShouldReturnErrorIfScopeIdFieldIsInvalid()
    {
        $post_data = array_merge($this->model::factory()->make()->toArray(), [
            "scope_id" => 10000000
        ]);
        $response = $this->withHeaders($this->headers)->post(route("{$this->route_prefix}.store"), $post_data);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    /**
     * Update tests
     *
     * 1. Using store route to update the resource.
     * 2. Using store route to update the resouce with non mandatory data.
    */

    public function testAdminCanUpdateResource()
    {
        $response = $this->withHeaders($this->headers)->post(route("{$this->route_prefix}.store"), $this->getUpdateData());

        $response->assertStatus(201);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.create-success", ["name" => $this->model_name])
        ]);
    }
}
