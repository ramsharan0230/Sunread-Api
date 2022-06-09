<?php

namespace Modules\Customer\Tests\Feature;

use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Configuration;
use Modules\Core\Tests\BaseTestCase;
use Modules\Country\Entities\City;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerAddress;

class CustomerAddressTest extends BaseTestCase
{
    public $customer, $channel;

    public function setUp(): void
    {
        $this->model = CustomerAddress::class;
        parent::setUp();

        $this->admin = $this->createAdmin();
        $this->model_name = "Customer Address";
        $this->route_prefix = "admin.customers.addresses";
        $this->customer = Customer::first();
        $this->channel = Channel::factory()->create(["website_id" => $this->customer->website_id]);
        $this->append_to_route = $this->customer->id;
    }

    public function getNonMandatoryCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "address2" => null
        ]);
    }

    public function getInvalidCreateData(): array
    {
        return array_merge($this->getCreateData(), [
            "address1" => null
        ]);
    }

    public function getCreateData(): array
    {
        $city = City::first();
        $region = $city->region()->first();
        $country = $region->country()->first();
        Configuration::factory()->make()->create([
            "scope" => "channel",
            "path" => "allow_countries",
            "scope_id" => $this->channel->id,
            "value" => ["{$country->iso_2_code}"],
        ]);

        return array_merge($this->model::factory()->make()->toArray(), [
            "customer_id" => $this->customer->id,
            "channel_id" => $this->channel->id,
            "country_id" => $country->id,
            "region_id" => $region->id,
            "city_id" => $city->id
        ]);
    }

    public function testAdminCanUpdateResourceWithDefaultAddress()
    {
        $post_data = [
            "default_shipping_address" => 1,
            "default_billing_address" => 1
        ];
        $response = $this->withHeaders($this->headers)->put($this->getRoute("defaultAddress", [$this->default_resource_id]), $post_data);

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.update-success", ["name" => $this->model_name])
        ]);
    }

    public function testAdminCanFetchIndividualDefaultAddresses()
    {
        $response = $this->withHeaders($this->headers)->get($this->getRoute("default"));

        $response->assertOk();
        $response->assertJsonFragment([
            "status" => "success",
            "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
        ]);
    }
}
