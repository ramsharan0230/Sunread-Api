<?php

 namespace Modules\Customer\Tests\Feature;

 use Illuminate\Foundation\Testing\DatabaseTransactions;
 use Modules\Core\Entities\Channel;
 use Modules\Core\Entities\Configuration;
 use Modules\Core\Entities\Store;
 use Modules\Core\Entities\Website;
 use Modules\Country\Entities\City;
 use Tests\TestCase;
 use Illuminate\Support\Facades\Auth;
 use Illuminate\Support\Facades\Hash;
 use Modules\Customer\Entities\Customer;
 use Modules\Customer\Entities\CustomerAddress;

 class CustomerAddressAccountTest extends TestCase
 {
     use DatabaseTransactions;

     protected object $customer, $fake_customer, $website, $channel, $store;
     protected array $headers;

     public $model, $route_prefix, $model_name, $type;

     public function setUp(): void
     {
         $this->model = CustomerAddress::class;
         parent::setUp();

         $this->website =  Website::factory()->create();
         $this->channel =  Channel::factory()->create([ "website_id" => $this->website->id ]);
         $this->store =  Store::factory()->create([ "channel_id" => $this->channel->id ]);

         $this->customer = $this->createCustomer();
         $this->model_name = "Customer Address";
         $this->route_prefix = "customers.address";
         $this->headers = [
             "hc-host" => $this->website->hostname,
             "hc-channel" => $this->channel->code,
             "hc-store" => $this->store->code,
         ];
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

     public function createCustomer(array $attributes = []): object
     {
         $password = $attributes["password"] ?? "password";

         $data = [
             "password" => Hash::make($password),
             "website_id" => $this->website->id,
             "is_email_verified" => 1,
         ];

         $customer = Customer::factory()->create($data);
         $token = $this->createToken($customer->email, $password);
         $this->headers["Authorization"] = "Bearer {$token}";

         return $customer;
     }

     public function createToken(string $customer_email, string $password): ?string
     {
         $jwtToken = Auth::guard("customer")
             ->setTTL( config("jwt.customer_jwt_ttl") )
             ->attempt([
                 "email" => $customer_email,
                 "password" => $password
             ]);
         return $jwtToken ?? null;
     }

     public function testCustomerCanAddOwnAddress()
     {
         $this->headers["hc-host"] = $this->website->hostname;
         $channel = Channel::inRandomOrder()->whereWebsiteId($this->website->id)->first();
         $this->headers["hc-channel"] = $channel->code;
         $this->headers["hc-store"] = Store::inRandomOrder()->whereChannelId($channel->id)->first()->code;

         $post_data["shipping"] = $this->getCreateData();
         $post_data["billing"] = $this->getCreateData();
         $response = $this->withHeaders($this->headers)->post(route("{$this->route_prefix}.create"), $post_data);

         $response->assertStatus(200);
         $response->assertJsonFragment([
             "status" => "success",
             "message" => __("core::app.response.create-success", ["name" => $this->model_name])
         ]);
     }

     public function testCustomerCanFetchOwnAddresses()
     {
         $response = $this->withHeaders($this->headers)->get(route("{$this->route_prefix}.show"));

         $response->assertStatus(200);
         $response->assertJsonFragment([
             "status" => "success",
             "message" => __("core::app.response.fetch-success", ["name" => $this->model_name])
         ]);
     }
 }
