<?php
namespace Modules\ClubHouse\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Modules\Core\Entities\Website;

class ClubHouseFactory extends Factory
{
    protected $model = \Modules\ClubHouse\Entities\ClubHouse::class;

    public function definition(): array
    {
        return [
            "position" => $this->faker->randomDigit(),
            "website_id" => Website::factory()->create()->id
        ];
    }
}

