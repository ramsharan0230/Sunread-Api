<?php
return [
    "base_image" => [
        "module" => "Modules\Product\Entities\ProductImage",
        "options" => 0,
        "pluck" => []
    ],
    "small_image" => [
        "module" => "Modules\Product\Entities\ProductImage",
        "options" => 0,
        "pluck" => []
    ],
    "thumbnail_image" => [
        "module" => "Modules\Product\Entities\ProductImage",
        "options" => 0,
        "pluck" => []
    ],
    "gallery" => [
        "module" => "Modules\Product\Entities\ProductImage",
        "options" => 0,
        "pluck" => []
    ],
    "sku" => [
        "module" => "Modules\Product\Entities\Product",
        "options" => 0,
        "pluck" => []
    ],
    "status" => [
        "module" => "Modules\Product\Entities\Product",
        "options" => 0,
        "pluck" => []
    ],
    "tax_class_id" => [
        "module" => "Modules\Tax\Entities\ProductTaxGroup",
        "options" => 1,
        "pluck" => [ "id", "name" ],
        "translations" => 0
    ],
    "quantity_and_stock_status" => [
        "module" => "Modules\Inventory\Entities\CatalogInventory",
        "options" => 0,
        "pluck" => []
    ],
    "category_ids" => [
        "module" => "Modules\Inventory\Entities\CatalogInventory",
        "options" => 0,
        "pluck" => []
    ],
    "features" => [
        "module" => "Modules\Product\Entities\Feature",
        "options" => 1,
        "pluck" => [ "id", "name" ],
        "translations" => 1
    ]
];