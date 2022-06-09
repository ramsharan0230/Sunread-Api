<?php
namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WebsiteFactory extends Factory
{
    protected $model = \Modules\Core\Entities\Website::class;

    public function definition(): array
    {
         return [
            'code' => $this->faker->name(),
            'name' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(),
            'hostname' => "test-" . substr(time(), 0, rand(5, 10)) . "-" . $this->faker->unique()->domainName(),
            "position" => $this->faker->randomDigit()
        ];
    }
}
