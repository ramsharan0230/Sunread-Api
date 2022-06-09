<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerGroup;
use Tests\TestCase;

class StoreFrontBaseTestCase extends TestCase
{
    use DatabaseTransactions;

    public $model, $model_name, $route_prefix, $filter, $default_resource, $default_resource_slug, $fake_resource, $factory_count, $append_to_route, $website, $channel, $store;
    protected array $headers;
    public $createFactories, $hasFilters, $hasIndexTest, $hasShowTest, $hasWebsiteHost, $hasChannel, $hasStore, $authentication;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory_count = 2;
        $this->default_resource = $this->model::latest('id')->first();
        $this->default_resource_slug = $this->default_resource->slug;
        $this->fake_resource = 0;
        $this->filter = [
            "sort_by" => "id",
            "sort_order" => "asc"
        ];

        $this->createFactories = true;
        $this->hasFilters = true;
        $this->hasIndexTest = true;
        $this->hasShowTest = true;

        $this->hasWebsiteHost = true;
        $this->hasChannel = true;
        $this->hasStore = true;
    }

    public function createHeader(): void
    {
        $this->website = Website::factory()->create();

        if ($this->hasWebsiteHost) $this->headers["hc-host"] = $this->website->hostname;
        if ($this->hasChannel) {
            $this->channel = $this->createChannel();
            $this->headers["hc-channel"] = $this->channel->code;
        }
        if ($this->hasStore) {
            $this->channel = $this->channel ?? $this->createChannel();
            $this->store = $this->createStore();
            $this->headers["hc-store"] = $this->store->code;
        }
    }

    public function createCustomer(array $attributes = []): ?object
    {
        if ($this->authentication) {
            $password = $attributes["password"] ?? "password";
            $verification_token = Str::random(30);

            $data = [
                "password" => Hash::make($password),
                "customer_group_id" => CustomerGroup::first()->id,
                "verification_token" => $verification_token
            ];

            $customer = Customer::factory()->create($data);
            $token = $this->createToken($customer->email, $password);
            $this->headers["Authorization"] = "Bearer {$token}";
        }

        return $customer ?? null;
    }

    public function createToken(string $admin_email, string $password): ?string
    {
        $jwtToken = Auth::guard("customer")
            ->setTTL( config("jwt.customer_jwt_ttl") )
            ->attempt([
                "email" => $admin_email,
                "password" => $password
            ]);
        return $jwtToken ?? null;
    }

    public function createChannel(): object
    {
        $channelData = array_merge(Channel::factory()->make()->toArray(), [
            "website_id" => $this->website->id
        ]);
        return Channel::create($channelData); 
    }

    public function createStore(): object
    {
        $storeData = array_merge(Store::factory()->make()->toArray(), [
            "channel_id" => $this->channel->id
        ]);
        return Store::create($storeData);
    }

    /*
     * Factory methods 
    */
    public function getCreateData(): array { return $this->model::factory()->make()->toArray(); }
    public function getDefaultResourceSlug(): object { return $this->default_resource_slug; }
    public function getNonMandatoryCreateData(): array { return $this->getCreateData(); }
    public function getInvalidCreateData(): array { return $this->getCreateData(); }

    public function getRoute(string $method, ?array $parameters = null): string
    {
        $parameters = $this->append_to_route ? array_merge([$this->append_to_route], $parameters ?? []) : $parameters;
        return route("{$this->route_prefix}.{$method}", $parameters);
    }

    /*
     * GET tests
     * 1. Assert if resource list can be fetched
     * 2. Assert if resource list can be fetched with filter
     * 3. Assert if individual resouce can be fetched
     * 4. Assert if application returns correct error for invalid resource id
    */

    public function testVisitorCanFetchResource()
    {
        if ( !$this->hasIndexTest ) $this->markTestSkipped("Index method not available.");
        if ( $this->createFactories ) $this->model::factory($this->factory_count)->create();

        $response = $this->withHeaders($this->headers)->get($this->getRoute("index"));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanFetchFilteredResources()
    {
        if ( !$this->hasIndexTest ) $this->markTestSkipped("Index method not available.");
        if ( !$this->hasFilters ) $this->markTestSkipped("Filters not available.");

        $response = $this->withHeaders($this->headers)->get($this->getRoute("index", $this->filter));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-list-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanFetchIndividualResource()
    {
        if ( !$this->hasShowTest ) $this->markTestSkipped("Show method not available.");

        $response = $this->withHeaders($this->headers)->get($this->getRoute("show", [$this->default_resource_slug]));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testShouldReturnErrorIfResourceDoesNotExist()
    {
        if ( !$this->hasShowTest ) $this->markTestSkipped("Show method not available.");

        $response = $this->withHeaders($this->headers)->get($this->getRoute("show", [$this->fake_resource]));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);
    }
}