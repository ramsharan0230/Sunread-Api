<?php
namespace Modules\Tax\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Tax\Entities\CustomerTaxGroup;
use Modules\Tax\Entities\ProductTaxGroup;
use Modules\Tax\Entities\TaxRule;

class TaxRuleFactory extends Factory
{
    protected $model = TaxRule::class;

    public function definition(): array
    {
        return [
            "name" => $this->faker->name(),
            "priority" => $this->faker->randomDigit(),
        ];
    }
}

