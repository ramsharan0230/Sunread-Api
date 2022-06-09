<?php
namespace Modules\Attribute\Database\factories;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Attribute\Entities\AttributeGroup;
use Modules\Core\Entities\Store;

class AttributeFactory extends Factory
{
    protected $model = \Modules\Attribute\Entities\Attribute::class;

    public function definition(): array
    {
        $name = $this->faker->name();
        $slug = $this->faker->unique()->slug();

        $type = Arr::random([
            "text",
            "textarea",
            "price",
            "boolean",
            "select",
            "datetime",
            "date",
            "checkbox",
            "texteditor"
        ]);
        $scope = Arr::random([ "website", "channel", "store" ]);

        return [
            "slug" => $slug,
            "name" => $name,
            "type" => $type,
            "scope" => $scope,
            "position" => rand(1,20),
            "is_required" => rand(0,1),
            "validation" => null,
            "is_visible_on_storefront" => rand(0,1),
            "is_user_defined" => 1,
            "default_value" => null
        ];
    }
}

