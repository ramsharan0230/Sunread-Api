<?php

namespace Modules\Customer\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Channel;
use Modules\Country\Entities\City;
use Modules\Customer\Entities\Customer;

class CustomerAddressFactory extends Factory
{
    protected $model = \Modules\Customer\Entities\CustomerAddress::class;

    public function definition(): array
    {
        $customer = Customer::first();
        $channel = Channel::factory()->create(["website_id" => $customer->website_id]);

        $city = City::first();
        $region = $city?->region()->first();
        $country = $region?->country()->first();

        return [
            "customer_id" => $customer->id,
            "first_name" => $this->faker->firstName(),
            "last_name" => $this->faker->lastName(),
            "address1" => $this->faker->address(),
            "address2" => $this->faker->address(),
            "address3" => $this->faker->address(),
            "country_id" => $country?->id,
            "region_id" => $region?->id,
            "city_id" => $city?->id,
            "postcode" => $this->faker->numerify("#####"),
            "phone" => $this->faker->phoneNumber(),
            "default_billing_address" => 1,
            "default_shipping_address" => 1,
            "channel_id" => $channel?->id
        ];
    }
}
