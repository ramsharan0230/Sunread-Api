<?php
namespace Modules\Sales\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Country\Entities\City;
use Modules\Country\Entities\Country;
use Modules\Country\Entities\Region;

class OrderAddressFactory extends Factory
{
    protected $model = \Modules\Sales\Entities\OrderAddress::class;

    public function definition()
    {
        return [
            "first_name" => $this->faker->firstName(),
            "last_name" => $this->faker->lastName(),
            "phone" => $this->faker->phoneNumber(),
            "email" => $this->faker->email(),
            "address_line_1" => $this->faker->address(),
            "postal_code" => $this->faker->postcode(),
            "country_id" => Country::first()->id,
            "region_id" => Region::first()->id,
            "city_id" => City::first()->id, 
        ];
    }
}

