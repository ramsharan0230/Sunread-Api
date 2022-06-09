<?php

return [
    "title" => "Cash On Delivery Payment",
    "slug" => "cash_on_delivery",
    "repository" => "Modules\PaymentCashOnDelivery\Repositories\CashOnDeliveryRepository",
    "elements" => [
        [
            "title" => "Enabled",
            "path" => "payment_methods_cash_on_delivery",
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
            "path" => "payment_methods_cash_on_delivery_title",
            "type" => "text",
            "provider" => "",
            "pluck" => [],
            "default" => "Cash On Delivery",
            "options" => [],
            "rules" => "",
            "scope" => "channel",
            "is_required" => 0,
            "sort_by" => ""
        ],
        [
            "title" => "New Order Status",
            "path" => "payment_methods_cash_on_delivery_new_order_status",
            "type" => "select",
            "provider" => "Modules\Sales\Entities\PendingOrderStatus",
            "pluck" => ["slug", "name"],
            "default" => "pending",
            "options" => [],
            "rules" => "exists:order_statuses,slug",
            "multiple" => false,
            "scope" => "channel",
            "is_required" => 1,
            "sort_by" => ""
        ],
        [
            "title" => "Payment From Applicable Countries",
            "path" => "payment_methods_cash_on_delivery_payment_from_applicable_countries",
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
            "title" => "Payment From Specific Countries",
            "path" => "payment_methods_cash_on_delivery_payment_from_specific_countries",
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
        ],
        [
            "title" => "Minimum Order Total",
            "path" => "payment_methods_cash_on_delivery_minimum_total_order",
            "type" => "text",
            "provider" => "",
            "pluck" => [],
            "default" => 0.00,
            "options" => [],
            "rules" => "",
            "scope" => "channel",
            "is_required" => 1,
            "sort_by" => ""
        ],
        [
            "title" => "Maximum Order Total",
            "path" => "payment_methods_cash_on_delivery_maximum_total_order",
            "type" => "text",
            "provider" => "",
            "pluck" => [],
            "default" => 0.00,
            "options" => [],
            "rules" => "",
            "scope" => "channel",
            "is_required" => 1,
            "sort_by" => ""
        ]
    ]
];
