<?php
namespace Modules\User\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = \Modules\User\Entities\Role::class;

    public function definition(): array
    {
        return [
            "name" => $this->faker->name(),
            "slug" => $this->faker->unique()->slug(),
            "description" => $this->faker->sentence(),
            "permission_type" => "custom",
            "permissions" => []
        ];
    }
}

