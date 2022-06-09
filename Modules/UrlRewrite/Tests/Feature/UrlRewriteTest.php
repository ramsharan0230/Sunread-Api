<?php

namespace Modules\UrlRewrite\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\UrlRewrite\Entities\UrlRewrite;

class UrlRewriteTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = UrlRewrite::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Url Rewrite";
        $this->route_prefix = "admin.url-rewrites";
    }

    public function testAdminCanFetchResources()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("index"));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "request_path" => null,
            "type" => "invalid",
            "parameter_id" => 0
        ]);
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "store_id" => null
        ]);
    }

    public function testAdminCanDeleteResource()
    {
        $response = $this->withHeaders($this->headers)->delete($this->getRoute("destroy", [$this->default_resource_id]));

        $response->assertOk();

        $check_resource = $this->model::whereId($this->default_resource_id)->first() ? true : false;
        $this->assertFalse($check_resource);
    }
}
