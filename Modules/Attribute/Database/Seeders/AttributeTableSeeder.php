<?php

namespace Modules\Attribute\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeOption;
use Modules\Attribute\Entities\AttributeTranslation;

class AttributeTableSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            [
                "name" => "Product Name",
                "slug" => "name",
                "type" => "text",
                "is_required" => 1,
                "is_searchable" => 1,
                "search_weight" => 6,
                "scope" => "store"
            ],
            [
                "name" => "SKU",
                "type" => "text",
                "is_required" => 1,
                "is_searchable" => 1,
                "search_weight" => 6,
                "is_unique" => 1,
                "is_visible_on_storefront" => 1,
                "comparable_on_storefront" => 1,
                "scope" => "website",
                "is_synchronized" => 0
            ],
            [
                "name" => "Price",
                "type" => "price",
                "validation" => "decimal",
                "is_required" => 1,
                "use_in_layered_navigation" => 1,
                "scope" => "channel"
            ],
            [
                "name" => "Cost",
                "type" => "price",
                "validation" => "decimal",
                "is_required" => 0,
                "use_in_layered_navigation" => 1,
                "scope" => "channel"
            ],
            [
                "name" => "Special Price",
                "slug" => "special_price",
                "type" => "price",
                "validation" => "decimal",
                "scope" => "channel"
            ],
            [
                "name" => "Special Price From Date",
                "slug" => "special_from_date",
                "type" => "date",
                "scope" => "channel"
            ],
            [
                "name" => "Special Price To Date",
                "slug" => "special_to_date",
                "type" => "date",
                "scope" => "channel"
            ],
            [
                "name" => "Quantity And Stock Status",
                "slug" => "quantity_and_stock_status",
                "type" => "select",
                "options" => [
                    [
                        "name" => "In stock"
                    ],
                    [
                        "name" => "Out of stock"
                    ]
                ],
                "default_value" => "In stock",
                "scope" => "website",
                "is_synchronized" => 0
            ],
            [
                "name" => "Has Weight",
                "slug" => "has_weight",
                "type" => "select",
                "options" => [
                    [
                        "name" => "Yes",
                        "code" => 1
                    ],
                    [
                        "name" => "No",
                        "code" => 0
                    ]
                ],
                "default_value" => "No",
                "scope" => "website"
            ],
            [
                "name" => "Weight",
                "type" => "text",
                "validation" => "decimal",
                "scope" => "website"
            ],
            [
                "name" => "Visibility",
                "type" => "select",
                "is_required" => 1,
                "default_value" => "Not Visible Individually",
                "options" => [
                    [
                        "name" => "Not Visible Individually",
                        "code" => "not_visible"
                    ],
                    [
                        "name" => "Catalog",
                        "code" => "catalog"
                    ],
                    [
                        "name" => "Search",
                        "code" => "search"
                    ],
                    [
                        "name" => "Catalog, Search",
                        "code" => "catalog_search"
                    ],
                ],
                "scope" => "store",
                "is_synchronized" => 0
            ],
            [
                "name" => "Tax Group",
                "slug" => "tax_class_id",
                "type" => "select",
                "is_required" => 1,
                "default_value" => "",
                "options" => [],
                "scope" => "channel"
            ],
            [
                "name" => "Categories",
                "slug" => "category_ids",
                "type" => "categoryselect",
                "scope" => "website"
            ],
            [
                "name" => "Set Product as New from Date",
                "slug" => "new_from_date",
                "type" => "date",
                "scope" => "channel"
            ],
            [
                "name" => "Set Product as New to Date",
                "slug" => "new_to_date",
                "type" => "date",
                "scope" => "channel"
            ],
            [
                "name" => "Description",
                "type" => "texteditor",
                "scope" => "store",
                "is_searchable" => 1,
                "search_weight" => 1
            ],
            [
                "name" => "Short Description",
                "slug" => "short_description",
                "type" => "texteditor",
                "scope" => "store",
                "is_searchable" => 1,
                "search_weight" => 1
            ],
            [
                "name" => "URL key",
                "slug" => "url_key",
                "type" => "text",
                "scope" => "store",
                "is_required" => 1,
                "is_searchable" => 1,
                "search_weight" => 1
            ],
            [
                "name" => "Meta Keywords",
                "slug" => "meta_keywords",
                "type" => "textarea",
                "scope" => "store",
                "is_required" => 0
            ],
            [
                "name" => "Meta Title",
                "slug" => "meta_title",
                "type" => "text",
                "scope" => "store",
                "is_required" => 0
            ],
            [
                "name" => "Meta Description",
                "slug" => "meta_description",
                "type" => "textarea",
                "scope" => "store",
                "is_required" => 0
            ],
            [
                "name" => "Product Status",
                "slug" => "status",
                "type" => "select",
                "options" => [
                    [
                        "name" => "Enabled",
                        "code" => 1
                    ],
                    [
                        "name" => "Disabled",
                        "code" => 0
                    ]
                ],
                "default_value" => "Yes",
                "scope" => "website",
                "is_required" => 1
            ],
            [
                "name" => "Color",
                "type" => "select",
                "use_in_layered_navigation" => 1,
                "default_value" => "",
                "is_user_defined" => 1,
                "options" => [
                    [
                        "name" => "Red",
                        "code" => "198",
                    ],
                    [
                        "name" => "Green",
                        "code" => "276",
                    ],
                    [
                        "name" => "Yellow",
                        "code" => "321",
                    ],
                    [
                        "name" => "Blue",
                        "code" => "423"
                    ]
                ],
                "is_synchronized" => 0
            ],
            [
                "name" => "Size",
                "type" => "select",
                "use_in_layered_navigation" => 1,
                "default_value" => "",
                "is_user_defined" => 1,
                "options" => [
                    [
                        "name" => "S",
                    ],
                    [
                        "name" => "M",
                    ],
                    [
                        "name" => "L",
                    ],
                    [
                        "name" => "XL",
                    ],
                ],
                "is_synchronized" => 0
            ],
            [
                "name" => "Specifications",
                "slug" => "erp_features",
                "type" => "texteditor",
                "scope" => "store",
                "is_searchable" => 1,
            ],
            [
                "name" => "Size and Care",
                "type" => "texteditor",
                "scope" => "store",
                "is_searchable" => 1,
            ],
            [
                "name" => "EAN Code",
                "type" => "text",
                "is_unique" => 1,
                "scope" => "store"
            ],
            [
                "name" => "Gallery",
                "slug" => "gallery",
                "type" => "productimages",
                "scope" => "website",
                "is_required" => 0,
                "is_synchronized" => 0
            ],
            [
                "name" => "Collection",
                "type" => "select",
                "use_in_layered_navigation" => 1,
                "default_value" => "Collection1",
                "options" => [
                    [
                        "name" => "Collection1",
                    ],
                    [
                        "name" => "Collection2",
                    ],
                    [
                        "name" => "Collection3",
                    ],
                    [
                        "name" => "Collection4",
                    ],
                ]
            ],
            [
                "name" => "Component",
                "slug" => "component",
                "type" => "builder",
                "scope" => "store",
                "is_synchronized" => 0
            ],
            // [
            //     "name" => "Animated Image",
            //     "slug" => "animated_image",
            //     "type" => "image",
            //     "scope" => "website",
            // ],
            // [
            //     "name" => "Disable Animation",
            //     "slug" => "disable_animation",
            //     "type" => "boolean",
            //     "scope" => "website",
            // ],
            [
                "name" => "Features",
                "slug" => "features",
                "type" => "multiselect",
                "default_value" => "",
                "options" => [],
                "scope" => "store"
            ],
            [
                "name" => "Fit",
                "type" => "texteditor",
                "scope" => "store"
            ],
            [
                "name" => "Constructed For",
                "type" => "texteditor",
                "scope" => "store"
            ],
            [
                "name" => "Technical Details",
                "type" => "texteditor",
                "scope" => "store"
            ],
            [
                "name" => "Measurements",
                "type" => "texteditor",
                "scope" => "store"
            ],
            [
                "name" => "Wash & Care",
                "type" => "texteditor",
                "scope" => "store"
            ],
            [
                "name" => "Factory",
                "type" => "texteditor",
                "scope" => "store"
            ],
            [
                "name" => "Delivery & Returns",
                "type" => "texteditor",
                "scope" => "store"
            ],
            [
                "name" => "Section Layout",
                "slug" => "section_layout",
                "type" => "select",
                "options" => [
                    [
                        "name" => "White",
                        "code" => "white"
                    ],
                    [
                        "name" => "Black",
                        "code" => "black"
                    ],
                ],
                "default_value" => "White",
                "scope" => "store",
            ],
            [
                "name" => "Erp Variant Code",
                "type" => "text",
                "scope" => "store",
                "is_required" => 0,
            ],
            [
                "name" => "Erp Guid",
                "slug" => "erp_guid",
                "type" => "text",
                "scope" => "website",
                "is_required" => 0
            ],
        ];



        array_map(function($attribute){
            $default_value = isset($attribute["default_value"]) && !in_array($attribute["type"], ["select", "multiselect", "checkbox"]) ? $attribute["default_value"] : null;
            $attribute_array = [
                "slug" => $attribute["slug"] ?? Str::slug($attribute["name"], "_"),
                "name" => $attribute["name"],
                "type" => $attribute["type"],
                "scope" => $attribute["scope"] ?? "website",
                "validation" => $attribute["validation"] ?? null,
                "is_required" => $attribute["is_required"] ?? 0,
                "is_unique" => $attribute["is_unique"] ?? 0,
                "use_in_layered_navigation" => $attribute["use_in_layered_navigation"] ?? 0,
                "comparable_on_storefront" => $attribute["comparable_on_storefront"] ?? 0,
                "is_searchable" => $attribute["is_searchable"] ?? 0,
                "search_weight" => $attribute["search_weight"] ?? null,
                "is_user_defined" => $attribute["is_user_defined"] ?? 0,
                "is_visible_on_storefront" => $attribute["is_visible_on_storefront"] ?? 0,
                "default_value" => $default_value,
                "is_synchronized" => $attribute["is_synchronized"] ?? 1,
            ];

            $attribute_data = Attribute::withoutEvents( function () use ($attribute_array) {
                return Attribute::create($attribute_array);
            });

            AttributeTranslation::create([
                "store_id" => 1,
                "name" => $attribute["name"],
                "attribute_id" => $attribute_data->id
            ]);

            if(isset($attribute["options"]))
            {
                $count = 0;
                array_map(function($attribute_option) use($attribute_data, $attribute, $count) {
                    global $count;
                    AttributeOption::withoutEvents( function () use ( $attribute_data, $count, $attribute_option, $attribute ) {
                        AttributeOption::create([
                            "attribute_id" => $attribute_data->id,
                            "name" => $attribute_option["name"],
                            "position" => ++$count,
                            "is_default" => ( $attribute["default_value"] == $attribute_option["name"] ) ? 1 : 0,
                            "code" => $attribute_option["code"] ?? null,
                        ]);
                    });
                }, $attribute["options"]);
            }

        }, $attributes);
    }
}
