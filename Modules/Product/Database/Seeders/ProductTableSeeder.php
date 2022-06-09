<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductAttribute;
use Modules\Attribute\Entities\Attribute;

class ProductTableSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            [
                "attribute_id" => $this->attributeId("name"),
                "value" => "Dell Laptop" 
            ],
            [
                "attribute_id" => $this->attributeId("price"),
                "value" => 750.00 
            ],
            [
                "attribute_id" => $this->attributeId("cost"),
                "value" => 750.00 
            ],
            [
                "attribute_id" => $this->attributeId("tax_class_id"),
                "value" => 1 
            ],
            [
                "attribute_id" => $this->attributeId("visibility"),
                "value" => 8
            ]
        ];
        
        $product = Product::withoutEvents( function () {
                return Product::create([
                    "attribute_set_id" => 1,
                    "sku" => "dell-laptop",
                    "type" => "simple",
                    "website_id" => 1,
                    "created_at" => now(),
                    "updated_at" => now()
                ]);
            });

        foreach($attributes as $attributeData)
        {
            $attribute = Attribute::find($attributeData["attribute_id"]);
            $attribute_type = config("attribute_types")[$attribute->type ?? "string"];
            $value = $attribute_type::create(["value" => $attributeData["value"]]);
            ProductAttribute::create([
                "attribute_id" => $attribute->id,
                "product_id"=> $product->id,
                "value_type" => $attribute_type,
                "value_id" => $value->id,
                "scope" => "website",
                "scope_id" => 1
            ]);
        }             
    }

    public function attributeId(string $slug): ?int
    {
        return Attribute::whereSlug($slug)->first()?->id;
    }
}
