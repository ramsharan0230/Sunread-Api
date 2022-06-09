<?php

return [
    "title" => "Free Shipping",
    "slug" => "free_shipping",
    "repository" => "Modules\DeliveryFreeShipping\Repositories\DeliveryFreeShippingRepository",
    "elements" => [
        [
            "title" => "Enabled",
            "path" => "delivery_methods_free_shipping",
            "type" => "select",
            "provider" => "",
            "pluck" => [],
            "default" => 1,
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
            "path" => "delivery_methods_free_shipping_title",
            "type" => "text",
            "provider" => "",
            "pluck" => [],
            "default" => "Free Shipping",
            "options" => [],
            "rules" => "",
            "multiple" => false,
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ],
        [
            "title" => "Method Name",
            "path" => "delivery_methods_free_shipping_method_name",
            "type" => "text",
            "provider" => "",
            "pluck" => [],
            "default" => "Fixed",
            "options" => [],
            "rules" => "",
            "multiple" => false,
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ],
        [
            "title" => "Minimum Order Amount",
            "path" => "delivery_methods_free_shipping_flat_price",
            "type" => "text",
            "provider" => "",
            "pluck" => [],
            "default" => 0.00,
            "options" => [],
            "rules" => "decimal",
            "multiple" => false,
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ],
        [
            "title" => "Include Tax to Amount",
            "path" => "delivery_methods_free_shipping_include_tax_to_amt",
            "type" => "select",
            "provider" => "",
            "pluck" => [],
            "default" => 1,
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
            "title" => "Ship to Applicable Countries",
            "path" => "delivery_methods_free_shipping_ship_from_applicable_countries",
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
            "path" => "delivery_methods_free_shipping_ship_from_specific_countries",
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
