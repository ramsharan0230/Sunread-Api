<?php

namespace Modules\SizeChart\Tests\Feature;

use Illuminate\Support\Arr;
use Modules\Core\Tests\BaseTestCase;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Entities\SizeChartContent;
use Modules\Core\Entities\Website;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SizeChartTest extends BaseTestCase
{
    public $filter;

    public function setUp(): void
    {
        $this->model = SizeChart::class;
        parent::setUp();
        $this->model_name = "Size Chart";
        $this->admin = $this->createAdmin();

        $this->route_prefix = "admin.sizecharts";
        $this->hasIndexTest = false;
        $this->filter = [
            "website_id" => Website::inRandomOrder()->first()->id,
        ];
        $this->testShouldReturnErrorIfResourceDoesNotExist = false;
    }

    public function testAdminCanFetchResources()
    {
        if ($this->createFactories) {
            $this->model::factory($this->factory_count)->create();
        }
        $response = $this->withHeaders($this->headers)->get($this->getRoute("index", $this->filter));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanCreateResource()
    {
        $post_data = $this->getCreateData();
        $response = $this->withHeaders($this->headers)->post($this->getRoute("store"), $post_data);

        $response->assertCreated();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.create-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanFetchIndividualResource()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("show", [$this->default_resource_id]));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanUpdateResource()
    {
        $post_data = $this->getUpdateData();
        // dd($post_data);
        $response = $this->withHeaders($this->headers)->put($this->getRoute("update", [$this->default_resource_id]), $post_data);

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }

    public function getUpdateData(): array
    {
        $websiteId = $this->filter;
        $updateData = $this->getCreateData();
        return array_merge($updateData, $websiteId);
    }

    public function testAdminCanDeleteResource()
    {
        $resource_id = $this->model::factory()->create()->id;
        $response = $this->withHeaders($this->headers)->delete($this->getRoute("destroy", [$this->default_resource_id]));

        $response->assertOk();

        $check_resource = $this->model::whereId($this->default_resource_id)->first() ? true : false;
        $this->assertFalse($check_resource);
    }

    public function testShouldReturnErrorIfResourceDoesNotExist()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("show", [rand(123, 987)]));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);
    }

    public function testShouldReturnErrorIfCreateDataIsInvalid()
    {
        $post_data = $this->getInvalidCreateData();
        $response = $this->withHeaders($this->headers)->post($this->getRoute("store"), $post_data);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            "status" => "error"
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "title" => null,
            "website_id" => null,
        ]);
    }

    public function getCreateData(): array
    {
        $data = $this->model::factory()->make()->toArray();
        $store_merged = array_merge($data, $this->getStores());
        $d = array_merge($store_merged, $this->getAttributes());
        return $d;
    }

    public function testAdminCanCreateResourceWithNonMandatoryData(): void
    {
        $post_data = $this->getNonMandatoryCreateData();

        $response = $this->withHeaders($this->headers)->post($this->getRoute("store"), $post_data);

        $response->assertCreated();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.create-success", ["name" => $this->model_name])
        ]);
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "slug" => null
        ]);
    }

    private function getAttributes(): array
    {
        Storage::fake();
        return array_merge($this->model::factory()->make()->toArray(), [
            "attributes" => [
                [
                    "title" => Str::random(10),
                    "slug" => Str::slug(Str::random(10)),
                    "type" => Arr::random(["editor", "image"]),
                    "content" => UploadedFile::fake()->image("image.png"),
                ]
            ]
        ]);

        // $data["attributes"][0]["title"]= Str::random(15);
        // $data["attributes"][0]["slug"]= Str::slug(Str::random(15), "-");
        // $data["attributes"][0]["type"]= Arr::random(SizeChartContent::$content_types);
        // if($data["attributes"][0]["type"] =="editor")
        //     $data["attributes"][0]["content"] = json_encode(Str::random(100));
        // else
        //     $data["attributes"][0]["content"]= UploadedFile::fake()->image("image.png");

        // return $data;
    }

    private function getStores(): array
    {
        $stores['stores'][0]=0;
        $stores['stores'][1]=0;

        return $stores;
    }
}