<?php
namespace Modules\Inventory\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Product\Entities\Product;
use Modules\Core\Entities\Website;

class CatalogInventoryFactory extends Factory
{
    protected $model = \Modules\Inventory\Entities\CatalogInventory::class;

    public function definition(): array
    {
        return [
            "product_id" => Product::factory()->create()->id,
            "website_id" => Website::factory()->create()->id,
            "quantity" => 10,
            "created_at" => now(),
            "updated_at" => now()
        ];
    }
}

