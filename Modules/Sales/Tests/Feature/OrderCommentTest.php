<?php

namespace Modules\Sales\Tests\Feature;

use Modules\Core\Tests\BaseTestCase;
use Modules\Sales\Entities\Order;
use Modules\Sales\Entities\OrderComment;

class OrderCommentTest extends BaseTestCase
{
    protected $order_id;

    public function setUp(): void
    {
        $this->model = OrderComment::class;
        parent::setUp();
        $this->admin = $this->createAdmin();
        $this->order_id = Order::first()->id;
        $this->createFactories = false;
        $this->hasFilters = false;
        $this->hasIndexTest = false;
        $this->hasShowTest = false;
        $this->hasStoreTest = false;
        $this->hasUpdateTest = false;
        $this->hasDestroyTest = false;

        $this->model_name = "Order Comment";
        $this->route_prefix = "admin.sales.comments";
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "comment" => null
        ]);
    }

    public function testAdminCanFetchResources()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("index", ["order_id" => $this->order_id ]));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanFetchIndividualResource()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("show", ["order_id" => $this->order_id, "comment_id" => $this->default_resource_id]));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testShouldReturnErrorIfResourceDoesNotExist()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("show", ["order_id" => $this->order_id, "comment_id" => $this->fake_resource_id]));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanCreateResource()
    {
        $post_data = $this->getCreateData();
        $response = $this->withHeaders($this->headers)->post($this->getRoute("store", ["order_id" => $this->order_id ]), $post_data);

        $response->assertCreated();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.create-success", ["name" => $this->model_name])
        ]);
    }

    public function testShouldReturnErrorIfCreateDataIsInvalid()
    {
        $post_data = $this->getInvalidCreateData();
        $response = $this->withHeaders($this->headers)->post($this->getRoute("store", ["order_id" => $this->order_id ]), $post_data);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testAdminCanUpdateResource()
    {
        $post_data = $this->getUpdateData();
        $response = $this->withHeaders($this->headers)->put($this->getRoute("update", ["order_id" => $this->order_id, "comment_id" => $this->default_resource_id]), $post_data);

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }

    public function testShouldReturnErrorIfUpdateDataIsInvalid()
    {
        $post_data = $this->getInvalidUpdateData();
        $response = $this->withHeaders($this->headers)->put($this->getRoute("update", ["order_id" => $this->order_id, "comment_id" => $this->default_resource_id]), $post_data);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function testAdminCanDeleteResource()
    {
        $resource_id = $this->model::factory()->create()->id;
        $response = $this->withHeaders($this->headers)->delete($this->getRoute("destroy", ["order_id" => $this->order_id, "comment_id" =>$resource_id]));

        $response->assertOk();

        $check_resource = $this->model::whereId($resource_id)->first() ? true : false;
        $this->assertFalse($check_resource);
    }


    
}
