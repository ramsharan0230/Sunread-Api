<?php

namespace Modules\NavigationMenu\Tests\Feature;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Modules\Core\Entities\Website;
use Modules\Core\Tests\BaseTestCase;
use Illuminate\Support\Facades\Storage;
use Modules\NavigationMenu\Entities\NavigationMenu;
use Modules\NavigationMenu\Entities\NavigationMenuItem;

class NavigationMenuItemTest extends TestCase
{
    // protected $default_resource;

    // public function setUp(): void
    // {
    //     $this->model = NavigationMenuItem::class;

    //     parent::setUp();
    //     $this->admin = $this->createAdmin();

    //     $this->model_name = "Navigation Menu Item";
    //     $this->route_prefix = "admin.navigation-menu.items";

    //     $this->model::factory(10)->create();

    //     $this->default_resource = $this->model::latest('id')->first();
    //     $this->default_resource_id = $this->default_resource->id;
    //     $this->hasStatusTest = false;
    //     $this->hasFilters = false;
    // }

    // public function getCreateData(): array
    // {
    //     Storage::fake();
    //     $title = Str::random(20);
    //     return array_merge($this->model::factory()->make()->toArray(), [
    //         "items" => [
    //             "title" => [
    //                 "value" => $title
    //             ],
    //             "status" => [
    //                 "value" => rand(0,1)
    //             ],
    //             "type" => [
    //                 "value" => "custom",
    //             ],
    //             "custom_link" => [
    //                 "value" => Str::random(40),
    //             ],
    //             "additional_data" => [
    //                 "value" => json_encode([])
    //             ],
    //         ]
    //     ]);
    // }

    // public function getUpdateData(): array
    // {
    //     $websiteId = $this->default_resource->navigationMenu->website_id;
    //     $updateData = $this->getCreateData();
    //     return array_merge($updateData, $this->getScope($websiteId));
    // }

    // public function testAdminCanFetchResources()
    // {
    //     if ( $this->createFactories ) $this->model::factory($this->factory_count)->create();

    //     $params = array_merge($this->filter, [
    //         "navigation_menu_id" => NavigationMenu::inRandomOrder()->first()->id,
    //         "item" => NavigationMenuItem::inRandomOrder()->first()->id,
    //     ]);

    //     $response = $this->withHeaders($this->headers)->get($this->getRoute("index", $params));

    //     $response->assertOk();
    //     $response->assertJsonFragment([
    //         "status" => "success",
    //         "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
    //     ]);
    // }

    // public function testAdminCanFetchIndividualResource()
    // {
    //     if ( !$this->hasShowTest ) $this->markTestSkipped("Show method not available.");

    //     $params = array_merge($this->filter, [
    //         "navigation_menu_id" => NavigationMenu::inRandomOrder()->first()->id,
    //         "item" => NavigationMenuItem::inRandomOrder()->first()->id,
    //     ]);

    //     $response = $this->withHeaders($this->headers)->get($this->getRoute("show", $params));

    //     $response->assertOk();
    //     $response->assertJsonFragment([
    //         "status" => "success",
    //         "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
    //     ]);
    // }

    // public function getInvalidCreateData(): array
    // {
    //     return array_merge($this->getCreateData(), [
    //         "items" => [
    //             "type" => [
    //                 "value" => "invalid",
    //             ],
    //         ]
    //     ]);
    // }

    // public function getNonMandatoryUpdateData(): array
    // {
    //     return array_merge($this->getUpdateData(), [
    //         "type_id" => null,
    //         "custom_link" => null,
    //     ]);
    // }

    // public function testAdminCanFetchResourceAttribute()
    // {
    //     $this->filter = [
    //         "scope" => Arr::random([ "website", "channel", "store" ])
    //     ];

    //     $response = $this->withHeaders($this->headers)->get($this->getRoute("attributes", $this->filter));

    //     $response->assertOk();
    //     $response->assertJsonFragment([
    //         "status" => "success",
    //         "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
    //     ]);
    // }
    // public function getScope($websiteId)
    // {
    //     $scope = Arr::random(["website", "channel", "store"]);
    //     $channels = Website::find($websiteId)->channels;
    //     if(count($channels) > 0 ){
    //         switch($scope)
    //         {
    //             case "website":
    //                 $scope_id = $websiteId;
    //                 break;

    //             case "channel":
    //                 $scope_id = $channels->first()->id;
    //                 break;

    //             case "store":
    //                 $stores = $channels->first()->stores;
    //                 $scope_id = (count($stores) > 0) ? $stores->first()->id : $this->getScope("channel", $websiteId);
    //                 break;
    //         }
    //     }
    //     return [
    //         "scope" => isset($scope_id) ? $scope : "website",
    //         "scope_id" => isset($scope_id) ? $scope_id : $websiteId
    //     ];

    // }
}
