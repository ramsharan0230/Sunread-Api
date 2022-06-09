<?php

namespace Modules\Category\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\Category\Entities\Category;
use Modules\Core\Entities\Store;
use Modules\Core\Tests\BaseTestCase;
use Illuminate\Support\Str;
use Modules\Category\Entities\CategoryValue;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Website;

class CategoryTest extends BaseTestCase
{
    protected int $root_category_id;
    protected $default_resource;

    public function setUp(): void
    {
        $this->model = Category::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Category";
        $this->route_prefix = "admin.catalog.categories";

        $this->model::factory(10)->create();
        $this->default_resource = $this->model::latest('id')->first();
        $this->default_resource_id = $this->default_resource->id;
        $this->root_category_id = $this->model::oldest('id')->first()->id;
        $this->hasFilters = false;
        $this->hasStatusTest = false;
    }

    public function getCreateData(): array
    {
        Storage::fake();
        return array_merge($this->model::factory()->make()->toArray(), [
            "items" => [
                "name" => [
                    "value" => Str::random(10)
                ],
                "image" => [
                    "value" => UploadedFile::fake()->image("image.png")
                ],
                "slug" => [
                    "value" => null
                ],
                "description" => [
                    "value" => Str::random(20)
                ],
                "meta_title" => [
                    "value" => Str::random(11)
                ],
                "meta_description" => [
                    "value" => Str::random(15)
                ],
                "meta_keywords" => [
                    "value" => Str::random(13)
                ],
                "status" => [
                    "value" => rand(0,1)
                ],
                "include_in_menu" => [
                    "value" => rand(0,1)
                ],
                "layout_type" => [
                    "value" => "single"
                ]
            ]
        ]);
    }

    public function getUpdateData(): array
    {
        $websiteId = $this->default_resource->website_id;
        $updateData = $this->getCreateData();
        unset($updateData["website_id"]);
        return array_merge($updateData, $this->getScope($websiteId));
    }

    public function testAdminCanFetchResources()
    {
        if ( $this->createFactories ) $this->model::factory($this->factory_count)->create();

        $websiteId = Website::inRandomOrder()->first()->id;
        $this->filter = array_merge($this->getScope($websiteId), [
            "website_id" => $websiteId
        ]);

        $response = $this->withHeaders($this->headers)->get($this->getRoute("index", $this->filter));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "parent_id" => null
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
        return array_merge($this->getUpdateData(),[
            "parent_id" => null
        ]);
    }

    public function testAdminCanFetchResourceAttribute()
    {
        $this->filter = [
            "scope" => Arr::random([ "website", "channel", "store" ])
        ];

        $response = $this->withHeaders($this->headers)->get($this->getRoute("attributes", $this->filter));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanUpdateResourceWithPosition()
    {
        $category = Category::inRandomOrder()->first();
        $post_data = [
            "parent_id" => !in_array($this->default_resource_id, [$category->id, $category->parent_id]) ? $category->id : null,
            "position" => rand(1,10),
            "scope" => "website",
            "scope_id" => $category->website_id
        ];
        $response = $this->withHeaders($this->headers)->put($this->getRoute("position", [$this->default_resource_id]), $post_data);

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }

    public function getScope($websiteId)
    {
        $scope = Arr::random([ "website", "channel", "store" ]);
        $channels = Website::find($websiteId)->channels;
        if(count($channels) > 0 ){
            switch($scope)
            {
                case "website":
                    $scope_id = $websiteId;
                    break;

                case "channel":
                    $scope_id = $channels->first()->id;
                    break;

                case "store":
                    $stores = $channels->first()->stores;
                    $scope_id = (count($stores) > 0) ? $stores->first()->id : $this->getScope("channel", $websiteId);
                    break;
            }
        }
        return [
            "scope" => isset($scope_id) ? $scope : "website",
            "scope_id" => isset($scope_id) ? $scope_id : $websiteId
        ];

    }
}
