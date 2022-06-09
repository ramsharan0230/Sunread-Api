<?php
namespace Modules\Erp\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Website;
use Modules\Country\Entities\Country;
use Modules\Erp\Entities\NavErpOrderMapper;

class NavErpOrderMapperFactory extends Factory
{
    protected $model = \Modules\Erp\Entities\NavErpOrderMapper::class;

    public function definition(): array
    {
        return [
            "website_id" => Website::first()->id,
            "title" => $this->faker->name(),
            "country_id" => Country::inRandomOrder()->first()->id,
            "nav_customer_number" => $this->faker->word(3),
            "shipping_account" => $this->faker->word(3),
            "discount_account" => $this->faker->word(3),
            "customer_price_group" => $this->faker->word(3),
            "is_default" => 1,
        ];
    }
}
