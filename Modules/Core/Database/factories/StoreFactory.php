<?php
namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Channel;

class StoreFactory extends Factory
{
    protected $model = \Modules\Core\Entities\Store::class;

    public function definition(): array
    {
        $name = $this->faker->name();
        $code = $this->faker->unique()->slug();
        $channel = Channel::factory()->create();

        return [
            "code" => $code,
            "name" => $name,
            "position" => $this->faker->randomDigit(),
            "channel_id" => $channel->id,
        ];
    }
}
