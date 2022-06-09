<?php

return [
    "title" => "Flat Rate Two",
    "slug" => "flat_rate_two",
    "repository" => "Modules\DeliveryFlatRateTwo\Repositories\DeliveryFlatRateRepository",
    "elements" => [
        [
            "title" => "Enabled",
            "path" => "delivery_methods_flat_rate_two",
            "type" => "select",
            "provider" => "",
            "pluck" => [],
            "default" => 0,
            "options" => [
                [ "value" => 1, "label" => "Yes" ],
                [ "value" => 0, "label" => "No" ]
            ],
            "rules" => "boolean",
            "multiple" => false,
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ],
        [
            "title" => "Title",
            "path" => "delivery_methods_flat_rate_two_title",
            "type" => "text",
            "provider" => "",
            "pluck" => [],
            "default" => "Flat Rate",
            "options" => [],
            "rules" => "",
            "scope" => "channel",
            "is_required" => 1,
            "sort_by" => ""
        ],
        [
            "title" => "Method Name",
            "path" => "delivery_methods_flat_rate_two_method_name",
            "type" => "text",
            "provider" => "",
            "pluck" => [],
            "default" => "Fixed",
            "options" => [],
            "rules" => "",
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ],
        [
            "title" => "Type",
            "path" => "delivery_methods_flat_rate_two_flat_type",
            "type" => "select",
            "provider" => "",
            "pluck" => [],
            "default" => "per_item",
            "options" => [
                [ "value" => "per_item", "label" => "Per Item" ],
                [ "value" => "per_order", "label" => "Per Order" ]
            ],
            "rules" => "in:per_item,per_order",
            "multiple" => false,
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ],
        [
            "title" => "Price",
            "path" => "delivery_methods_flat_rate_two_flat_price",
            "type" => "text",
            "provider" => "",
            "pluck" => [],
            "default" => "50.00",
            "options" => [],
            "rules" => "decimal",
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ],
        [
            "title" => "Ship to Applicable Countries",
            "path" => "delivery_methods_flat_rate_two_ship_from_applicable_countries",
            "type" => "select",
            "provider" => "",
            "pluck" => [],
            "default" => "all_allowed_countries",
            "options" => [
                [ "value" => "all_allowed_countries", "label" => "All Allowed Countries" ],
                [ "value" => "specific_countries", "label" => "Specific Counrtry" ]
            ],
            "rules" => "in:all_allowed_countries,specific_countries",
            "multiple" => false,
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ],
        [
            "title" => "Ship From Specific Countries",
            "path" => "delivery_methods_flat_rate_two_ship_from_specific_countries",
            "type" => "select",
            "provider" => "Modules\Country\Entities\Country",
            "pluck" => ["name", "iso_2_code"],
            "default" => "",
            "options" => [],
            "rules" => "exists:countries,iso_2_code",
            "multiple" => false,
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ]
    ]
];
