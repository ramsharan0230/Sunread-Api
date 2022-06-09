<?php
namespace Modules\Attribute\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Attribute\Entities\AttributeSet;

class AttributeGroupFactory extends Factory
{
    protected $model = \Modules\Attribute\Entities\AttributeGroup::class;

    public function definition(): array
    {
        $name = $this->faker->name();
    
        return [
            "attribute_set_id" => AttributeSet::factory()->create()->id,
            "name" => $name,
            "position" => rand(1,20)
        ];
    }
}

