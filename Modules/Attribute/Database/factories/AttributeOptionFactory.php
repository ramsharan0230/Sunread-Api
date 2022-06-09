<?php
namespace Modules\Attribute\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Attribute\Entities\Attribute;

class AttributeOptionFactory extends Factory
{
    protected $model = \Modules\Attribute\Entities\AttributeOption::class;

    public function definition(): array
    {
        return [
            "attribute_id" => Attribute::factory()->create()->id,
            "name" => $this->faker->name(),
            "position" => rand(1,20),
            "code" => $this->faker->unique()->text(8)
        ];
    }
}

