<?php
namespace Modules\UrlRewrite\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;

class UrlRewriteFactory extends Factory
{
    protected $model = \Modules\UrlRewrite\Entities\UrlRewrite::class;

    public function definition(): array
    {
        $types = [ "Product" ];
        $type = Arr::random($types);

        switch ($type) {
            case "Product":

                $product = Product::withoutEvents(function () {
                    return Product::factory()->create();
                });
                
                $parameter_id = $product->id;

                $request_path = $this->faker->unique()->slug();
                break;

            case "Category":
                $category = Category::withoutEvents(function (){
                    return Category::factory()->create();
                });

                $parameter_id = $category->id;
                $request_path = $category->slug;
                break;
        }

        return [
            "type" => $type,
            "parameter_id" => $parameter_id,
            "request_path" => $request_path,
            'store_id' => isset($store_id) ? $store_id : null,
            "created_at" => now(),
            "updated_at" => now()
        ];
    }
}

