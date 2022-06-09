<?php
namespace Modules\Product\Database\factories;

use Modules\Brand\Entities\Brand;
use Modules\Product\Entities\Product;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Attribute\Entities\AttributeGroup;
use Modules\Attribute\Entities\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Entities\Website;

class ProductFactory extends Factory
{
    protected $model = \Modules\Product\Entities\Product::class;

    public function definition(): array
    {
        $attribute_set_id = AttributeSet::factory()->create()->id;

        $system_defined_attributes = Attribute::whereIsUserDefined(0)->pluck('id')->toArray();
        $user_defined_attributes = Attribute::factory()->create()->id;

        AttributeGroup::factory()
        ->create([
            "attribute_set_id" => $attribute_set_id,
            "position" => 1
        ])
        ->each(function ($attr_group) use ( $system_defined_attributes, $user_defined_attributes ) {
            $attr_group->attributes()->sync(array_merge($system_defined_attributes, [ $user_defined_attributes ]));
        });

        return [
            "parent_id" => null,
            "brand_id" => Brand::factory()->create()->id,
            "website_id" => Website::factory()->create()->id,
            "attribute_set_id" => $attribute_set_id,
            "type" => "simple"
        ];
    }

    public function configurable(): Self
    {
        return $this->state(function(array $attributes) {
            return [
                "parent_id" => null,
                "type" => "configurable"
            ];
        });
    }
}

