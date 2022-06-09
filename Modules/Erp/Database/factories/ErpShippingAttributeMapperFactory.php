<?php
namespace Modules\Erp\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Website;

class ErpShippingAttributeMapperFactory extends Factory
{
    protected $model = \Modules\Erp\Entities\ErpShippingAttributeMapper::class;

    public function definition(): array
    {
        return [
            "website_id" => Website::first()->id,
            "shipping_agent_code" => $this->faker->name(),
            "shipping_agent_service_code" => $this->faker->word(3),
            "shipping_method_code" => $this->faker->word(2),
        ];
    }
}