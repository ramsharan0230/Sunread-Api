<?php
namespace Modules\Customer\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Customer\Entities\CustomerGroup;

class CustomerFactory extends Factory
{
    protected $model = \Modules\Customer\Entities\Customer::class;

    public function definition(): array
    {
        $gender = Arr::random([ "male", "female", "other" ]);
        $store_id = Store::first()->id;
        return [
            "customer_group_id" => CustomerGroup::factory()->create()->id,
            "website_id" => Website::factory()->create()->id,
            "first_name" => $this->faker->firstName(),
            "last_name" => $this->faker->lastName(),
            "gender" => $gender,
            "date_of_birth" => $this->faker->dateTimeBetween('1950-01-01', '2012-12-31'),
            "email" => $this->faker->unique()->safeEmail(),
            "status" => 1,
            "password" => Hash::make("password"),
            "subscribed_to_news_letter" => 0,
            "remember_token" => Str::random(10),
            "account_type" => Arr::random(["personal", "company"]),
            "store_id" => $store_id
        ];
    }
}
