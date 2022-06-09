<?php
namespace Modules\Customer\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Tax\Entities\CustomerTaxGroup;

class CustomerGroupFactory extends Factory
{
    protected $model = \Modules\Customer\Entities\CustomerGroup::class;

    public function definition(): array
    {
        return [
            "name" => $this->faker->name(),
            "slug" => $this->faker->unique()->slug(),
            "is_user_defined" => 0,
            "customer_tax_group_id" => CustomerTaxGroup::factory()->create()->id
        ];
    }
}
