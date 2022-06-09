<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Core\Tests\BaseTestCase;

class ResolverTest extends BaseTestCase
{
    public function setUp(): void
    {
        $this->model = Website::class;

        parent::setUp();

        $this->model_name = "Website";
        $this->route_prefix = "public.resolver";

        $this->createFactories = false;
        $this->hasFilters = false;
        $this->hasIndexTest = false;
        $this->hasShowTest = false;
        $this->hasStoreTest = false;
        $this->hasUpdateTest = false;
        $this->hasDestroyTest = false;
    }

    public function testWebsiteCanBeResolved(): void
    {
        $response = $this->get($this->getRoute("resolve"));
        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testCustomWebsiteCanBeResolved(): void
    {
        $response = $this->get($this->getRoute("resolve", Website::inRandomOrder()->first()->host_name));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testCustomWebsiteShouldNotBeResolvedIfInvalid(): void
    {
        $this->headers["hc-host"] = "random-non-existent.domain";
        $response = $this->withHeaders($this->headers)->get($this->getRoute("resolve"));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => $this->model_name])
        ]);
    }

    public function testChannelCanBeResolved(): void
    {
        $website = Website::first();
        $this->headers["hc-host"] = $website->hostname;
        $channel_code = $website->channels->first()->code;
        $this->headers["hc-channel"] = $channel_code;

        $response = $this->withHeaders($this->headers)->get($this->getRoute("resolve"));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testChannelShouldNotBeResolvedIfInvalid(): void
    {
        $this->headers["hc-channel"] = "random-non-existent-channel-code";
        $response = $this->withHeaders($this->headers)->get($this->getRoute("resolve"));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => "Channel"])
        ]);
    }

    public function testStoreCanBeResolved(): void
    {
        $website = Website::first();
        $this->headers["hc-host"] = $website->hostname;
        $channel = Channel::inRandomOrder()->whereWebsiteId($website->id)->first();
        $this->headers["hc-channel"] = $channel->code;
        $this->headers["hc-store"] = Store::inRandomOrder()->whereChannelId($channel->id)->first()->code;
        $response = $this->withHeaders($this->headers)->get($this->getRoute("resolve"));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }

    public function testStoreShouldNotBeResolvedIfInvalid(): void
    {
        $this->headers["hc-store"] = "random-non-existent-store-code";
        $response = $this->withHeaders($this->headers)->get($this->getRoute("resolve"));

        $response->assertNotFound();
        $response->assertJsonFragment([
            "status" => "error",
            "message" => __("core::app.response.not-found", ["name" => "Store"])
        ]);
    }
}
