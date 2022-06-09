<?php

namespace Modules\Product\Tests\Feature\StoreFront;

use Modules\Core\Tests\StoreFrontBaseTestCase;
use Modules\Product\Entities\Product;

class ProductTest extends StoreFrontBaseTestCase
{
    public function setUp(): void
    {
        $this->model = Product::class;

        parent::setUp();

        $this->model_name = "Product";
        $this->route_prefix = "public.products";

        $this->createFactories = false;
        $this->hasFilters = false;
        $this->hasIndexTest = false;
        $this->hasShowTest = false;
        $this->hasChannel = false;

        $this->createHeader();
    }

    // public function testVisitorCanFetchIndividualResource()
    // {
    //     $default_headers = [
    //         "hc-host" => "international.co",
    //         "hc-channel" => "international",
    //         "hc-store" => "international-store" 
    //     ];
    //     $response = $this->withHeaders($default_headers)->get($this->getRoute("show", [$this->default_resource->id]));

    //     $response->assertOk();
    //     $response->assertJsonFragment([
    //         "status" => "success",
    //         "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
    //     ]);
    // }

    // public function testShouldReturnErrorIfResourceDoesNotExist()
    // {
    //     $default_headers = [
    //         "hc-host" => "international.co",
    //         "hc-channel" => "international",
    //         "hc-store" => "international-store" 
    //     ];
    //     $response = $this->withHeaders($default_headers)->get($this->getRoute("show", [rand(10,100)]));

    //     $response->assertNotFound();
    //     $response->assertJsonFragment([
    //         "status" => "error",
    //         "message" => __("core::app.response.not-found", ["name" => $this->model_name])
    //     ]);
    // }
}
