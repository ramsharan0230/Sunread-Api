<?php
namespace Modules\Attribute\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeSetFactory extends Factory
{
    protected $model = \Modules\Attribute\Entities\AttributeSet::class;

    public function definition(): array
    {
        $name = $this->faker->name();
        return [
            "name" => $name
        ];
    }
}

