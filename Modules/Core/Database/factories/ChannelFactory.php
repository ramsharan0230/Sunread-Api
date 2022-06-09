<?php
namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Website;

class ChannelFactory extends Factory
{
    protected $model = \Modules\Core\Entities\Channel::class;

    public function definition(): array
    {
        $website = Website::factory()->create();
        $code = $this->faker->unique()->slug();

        return [
            "code" => $code,
            "hostname" => $this->faker->unique()->domainName(),
            "name" => $this->faker->company(),
            "description" => $this->faker->paragraph(),
            "default_store_id" => null,
            "website_id" => $website->id
        ];
    }
}
