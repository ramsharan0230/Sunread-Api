<?php

namespace Modules\ClubHouse\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\ClubHouse\Entities\ClubHouse;
use Modules\Core\Tests\BaseTestCase;
use Illuminate\Support\Str;
use Modules\Core\Entities\Website;

class ClubHouseTest extends BaseTestCase
{
    protected $default_resource;

    public function setUp(): void
    {
        $this->model = ClubHouse::class;

        parent::setUp();
        $this->admin = $this->createAdmin();

        $this->model_name = "Club House";
        $this->route_prefix = "admin.clubhouses";

        $this->model::factory(10)->create();

        $this->default_resource = $this->model::latest('id')->first();
        $this->default_resource_id = $this->default_resource->id;
        $this->hasStatusTest = false;
        $this->hasFilters = false;
    }

    public function getCreateData(): array
    {
        Storage::fake();
        return array_merge($this->model::factory()->make()->toArray(), [
            "items" => [
                "title" => [
                    "value" => Str::random(10)
                ],
                "status" => [
                    "value" => rand(0,1)
                ],
                "slug" => [
                    "value" => Str::slug(Str::random(10))
                ],
                "type" => [
                    "value" => Arr::random(["clubhouse", "resort"]),
                ],
                "header_content" => [
                    "value" => Str::random(30)
                ],
                "opening_hours" => [
                    "value" => Str::random(10)
                ],
                "address" => [
                    "value" => Str::random(10)
                ],
                "contact" => [
                    "value" => Str::random(10)
                ],
                "latitude" => [
                    "value" => Str::random(10),
                ],
                "longitude" => [
                    "value" => Str::random(10),
                ],
                "thumbnail" => [
                    "value" => UploadedFile::fake()->image("image.png")
                ],
                "background_image" => [
                    "value" => UploadedFile::fake()->image("image.png")
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
            ]
        ]);
    }

    public function getUpdateData(): array
    {
        $websiteId = $this->default_resource->website_id;
        $updateData = $this->getCreateData();
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

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "website_id" => null
        ]);
    }

    public function getNonMandatoryUpdateData(): array
    {
        return array_merge($this->getUpdateData(), [
            "latitude" => null,
            "latitude" => null,
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
    public function getScope($websiteId)
    {
        $scope = Arr::random(["website", "channel", "store"]);
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
