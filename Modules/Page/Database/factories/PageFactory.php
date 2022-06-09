<?php
namespace Modules\Page\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Modules\Core\Entities\Website;
use Modules\Page\Entities\Page;

class PageFactory extends Factory
{
    protected $model = \Modules\Page\Entities\Page::class;

    public function definition(): array
    {
        return [
            "title" => $this->faker->name(),
            "slug" => $this->faker->unique()->slug(),
            "meta_title" => $this->faker->name(),
            "meta_keywords" => $this->faker->name(),
            "meta_description" => $this->faker->paragraph(),
            "position" => $this->faker->randomDigit(),
            "layout_type" => Arr::random(Page::$layout_types),
            "website_id" => Website::factory()->create()->id,
            "status" => 1,
        ];
    }
}
