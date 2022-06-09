<?php
return[
    "general" => [
        "title" => "General",
        "position" => 1,
        "children" => [
            [
                "title" => "General",
                "slug" => "general",
                "subChildren" => [
                    [
                        "title" => "Country Options",
                        "slug" => "country_options",
                        "elements" => [
                            [
                                "title" => "Default Country",
                                "path" => "default_country",
                                "type" => "select",
                                "provider" => "Modules\Country\Entities\Country",
                                "pluck" => ["name","iso_2_code"],
                                "default" => "SE",
                                "options" => [],
                                "rules" => "exists:countries,iso_2_code",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Allow Countries",
                                "path" => "allow_countries",
                                "type" => "select",
                                "provider" => "Modules\Country\Entities\Country",
                                "pluck" => ["name", "iso_2_code"],
                                "default" => ["SE","DZ"],
                                "options" => [],
                                "rules" => "array",
                                "value_rules" => "exists:countries,iso_2_code",
                                "multiple" => true,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ]
                        ]
                    ],

                    [
                        "title" => "Store Information",
                        "slug" => "store_information",
                        "elements" => [
                            [
                                "title" => "Store Name",
                                "path" => "store_name",
                                "type" => "select",
                                "provider" => "Modules\Core\Entities\Store",
                                "pluck" => ["name", "id"],
                                "default" => "",
                                "options" => [],
                                "rules" => "exists:stores,id",
                                "multiple" => false,
                                "scope" => "global",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Store Phone Number",
                                "path" => "store_phone_number",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "channel",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Store Hours of Operation",
                                "path" => "store_hours_operation",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "website",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Country",
                                "path" => "store_country",
                                "type" => "select",
                                "provider" => "Modules\Country\Entities\Country",
                                "pluck" => ["name", "iso_2_code"],
                                "default" => "",
                                "options" => [],
                                "rules" => "exists:countries,iso_2_code",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Region/State",
                                "path" => "store_region",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "global",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Zip/Postal Code",
                                "path" => "store_zip_code",
                                "type" => "number",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "numeric",
                                "scope" => "store",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "City",
                                "path" => "store_city",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "global",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Street Address",
                                "path" => "store_street_address",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "website",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Street Address Line 2",
                                "path" => "store_address_line2",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "channel",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Store Icon",
                                "path" => "store_icon",
                                "type" => "file",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "mimes:jpeg,jpg,bmp,png,svg",
                                "scope" => "store",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Pagination Limit",
                                "path" => "pagination_limit",
                                "type" => "number",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "numeric",
                                "scope" => "global",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Store Vat Number",
                                "path" => "store_vat_number",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "global",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Store Email Address",
                                "path" => "store_email_address",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "global",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Store Email Logo Url",
                                "path" => "store_email_logo_url",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "global",
                                "is_required" => 1,
                                "sort_by" => ""
                            ]
                        ]
                    ],
                    [
                        "title" => "Locale Options",
                        "slug" => "locale_options",
                        "elements" => [
                            [
                                "title" => "Locale",
                                "path" => "store_locale",
                                "type" => "select",
                                "provider" => "Modules\Core\Entities\Locale",
                                "pluck" => ["name", "id"],
                                "default" => "232",
                                "options" => [],
                                "rules" => "exists:locales,id",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Weight Unit",
                                "path" => "locale_weight_unit",
                                "type" => "select",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "kgs",
                                "options" => [
                                    [ "value" => "lbs", "label" => "lbs" ],
                                    [ "value" => "kgs", "label" => "kgs" ],
                                ],
                                "rules" => "in:lbs,kgs",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Global Timezone",
                                "path" => "global_timezone",
                                "type" => "select",
                                "provider" => "Modules\Core\Entities\TimeZone",
                                "pluck" => ["name", "id"],
                                "default" => "",
                                "options" => [],
                                "rules" => "exists:time_zones,id",
                                "multiple" => false,
                                "scope" => "website",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Channel Time Zone",
                                "path" => "channel_time_zone",
                                "type" => "select",
                                "provider" => "Modules\Core\Entities\TimeZone",
                                "pluck" => ["name", "id"],
                                "default" => "",
                                "options" => [],
                                "rules" => "exists:time_zones,id",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],

                        ]
                    ]
                ]
            ],
            [
                "title" => "Web",
                "slug" => "web",
                "subChildren" => [
                    [
                        "title" => "General",
                        "slug" => "general",
                        "elements" => [
                            [
                                "title" => "Logo",
                                "path" => "logo",
                                "type" => "file",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "mimes:jpeg,jpg,bmp,png,svg",
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Footer Logo",
                                "path" => "footer_logo",
                                "type" => "file",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "mimes:jpeg,jpg,bmp,png,svg",
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => "",
                            ],
                            [
                                "title" => "Favicon",
                                "path" => "favicon",
                                "type" => "file",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => ",svgmimes:jpeg,jpg,bmp,png",
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Channel Icon",
                                "path" => "channel_icon",
                                "type" => "file",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "mimes:jpeg,jpg,bmp,png,svg",
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => ""
                            ]
                        ]
                    ],
                    [
                        "title" => "Default Pages",
                        "slug" => "default_pages",
                        "elements" => [
                            [
                                "title" => "Home Page",
                                "path" => "home_page",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "channel",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "404 Page",
                                "path" => "404_page",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "channel",
                                "is_required" => 0,
                                "sort_by" => ""
                            ]
                        ]
                    ],
                    [
                        "title" => "Base URLs",
                        "slug" => "base_urls",
                        "elements" => [
                            [
                                "title" => "StoreFront Base URL",
                                "path" => "storefront_base_urL",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "https://sr.hbgdevlab.com/",
                                "options" => [],
                                "rules" => "",
                                "scope" => "website",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Admin Dashboard URL",
                                "path" => "admin_dashboard_url",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "website",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Media URL",
                                "path" => "media_url",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "website",
                                "is_required" => 0,
                                "sort_by" => ""
                            ]
                        ]
                    ]
                ]
            ],
            [
                "title" => "Currency Setup",
                "slug" => "currency_setup",
                "subChildren" => [
                    [
                        "title" => "Currency Options",
                        "slug" => "currency_options",
                        "elements" => [
                            [
                                "title" => "Channel Currency",
                                "path" => "channel_currency",
                                "type" => "select",
                                "provider" => "Modules\Core\Entities\Currency",
                                "pluck" => ["code","id"],
                                "default" => "142",
                                "options" => [],
                                "rules" => "exists:currencies,id",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Symbol Position",
                                "path" => "symbol_position",
                                "type" => "select",
                                "provider" => "",
                                "pluck" => [],
                                "default" => 4,
                                "options" => [
                                    [ "value" => 1, "label" => "Before Value" ],
                                    [ "value" => 2, "label" => "Before Value With Space" ],
                                    [ "value" => 3, "label" => "After Value" ],
                                    [ "value" => 4, "label" => "After Value With Space" ],
                                ],
                                "rules" => "in:1,2,3,4",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Minus Sign",
                                "path" => "minus_sign",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "-",
                                "options" => [],
                                "rules" => "",
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Minus Sign Position",
                                "path" => "minus_sign_position",
                                "type" => "select",
                                "provider" => "",
                                "pluck" => [],
                                "default" => 1,
                                "options" => [
                                    [ "value" => 1, "label" => "Before Value" ],
                                    [ "value" => 2, "label" => "Before Symbol" ],
                                    [ "value" => 3, "label" => "After Value" ],
                                    [ "value" => 4, "label" => "After Symbol" ]
                                ],
                                "rules" => "in:1,2,3,4",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Group Seperator",
                                "path" => "group_seperator",
                                "type" => "select",
                                "provider" => "",
                                "pluck" => [],
                                "default" => 2,
                                "options" => [
                                    [ "value" => 1, "label" => "Comma (,)" ],
                                    [ "value" => 2, "label" => "Dot (.)" ],
                                    [ "value" => 3, "label" => "Space()" ],
                                    [ "value" => 4, "label" => "None" ]
                                ],
                                "rules" => "in:1,2,3,4",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Decimal Seperator",
                                "path" => "decimal_seperator",
                                "type" => "select",
                                "provider" => "",
                                "pluck" => [],
                                "default" => 1,
                                "options" => [
                                    [ "value" => 1, "label" => "Comma (,)" ],
                                    [ "value" => 2, "label" => "Dot (.)" ]
                                ],
                                "rules" => "in:1,2",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => ""
                            ]
                        ]
                    ],
                ]
            ],
            [
                "title" => "Website Defaults",
                "slug" => "website_defaults",
                "subChildren" => [
                    [
                        "title" => "Channel",
                        "slug" => "channel",
                        "elements" => [
                            [
                                "title" => "Default Channel",
                                "path" => "website_default_channel",
                                "type" => "select",
                                "provider" => "Modules\Core\Entities\Channel",
                                "pluck" => ["code","id"],
                                "default" => "",
                                "options" => [],
                                "rules" => "exists:channels,id",
                                "multiple" => false,
                                "scope" => "website",
                                "is_required" => 0,
                                "sort_by" => "name"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "title" => "Email",
                "slug" => "email",
                "subChildren" => [
                    [
                        "title" => "General",
                        "slug" => "general",
                        "elements" => [
                            [
                                "title" => "Sender Name",
                                "path" => "email_sender_name",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "store",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Sender Email",
                                "path" => "email_sender_email",
                                "type" => "text",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "",
                                "options" => [],
                                "rules" => "",
                                "scope" => "store",
                                "is_required" => 0,
                                "sort_by" => ""
                            ]
                        ]
                    ],
                    [
                        "title" => "Templates",
                        "slug" => "templates",
                        "elements" => [
                            [
                                "title" => "Header",
                                "path" => "header",
                                "type" => "select",
                                "provider" => "Modules\EmailTemplate\Entities\HeaderTemplate",
                                "pluck" => [ "name", "id" ],
                                "default" => 1,
                                "options" => [],
                                "rules" => "exists:email_templates,id",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Footer",
                                "path" => "footer",
                                "type" => "select",
                                "provider" => "Modules\EmailTemplate\Entities\FooterTemplate",
                                "pluck" => [ "name", "id" ],
                                "default" => 2,
                                "options" => [],
                                "rules" => "exists:email_templates,id",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ]
                        ]
                    ],
                ]
            ]

        ]
    ],
    "customer" => [
        "title" => "Customer",
        "position" => 2,
        "children" => [
            [
                "title" => "Customer",
                "slug" => "customer",
                "subChildren" => [
                    [
                        "title" => "New Account Options",
                        "slug" => "new_account_options",
                        "elements" => [
                            [
                                "title" => "Default Customer Group",
                                "path" => "customer_default_customer_group",
                                "type" => "select",
                                "provider" => "Modules\Customer\Entities\CustomerGroup",
                                "pluck" => ["name", "id"],
                                "default" => "1",
                                "options" => [],
                                "rules" => "exists:customer_groups,id",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Require Email Confirmation",
                                "path" => "require_email_confirmation",
                                "type" => "select",
                                "provider" => "",
                                "pluck" => [],
                                "default" => 1,
                                "options" => [
                                    [ "value" => 1, "label" => "Yes" ],
                                    [ "value" => 2, "label" => "No" ],
                                ],
                                "rules" => "",
                                "multiple" => false,
                                "scope" => "website",
                                "is_required" => 1,
                                "sort_by" => ""
                            ]
                        ]
                    ],
                    [
                        "title" => "Password Options",
                        "slug" => "password_options",
                        "elements" => [
                            [
                                "title" => "Recovery Link Expiration Period (hours)",
                                "path" => "recovery_link_expiration_period",
                                "type" => "number",
                                "provider" => "",
                                "pluck" => [],
                                "default" => 2,
                                "options" => [],
                                "rules" => "",
                                "multiple" => false,
                                "scope" => "global",
                                "is_required" => 0,
                                "sort_by" => ""
                            ],
                            [
                                "title" => "Minimum Password Length",
                                "path" => "minimum_password_length",
                                "type" => "number",
                                "provider" => "",
                                "pluck" => [],
                                "default" => 8,
                                "options" => [],
                                "rules" => "",
                                "multiple" => false,
                                "scope" => "global",
                                "is_required" => 1,
                                "sort_by" => ""
                            ]
                        ]
                    ],
                    [
                        "title" => "Email Templates",
                        "slug" => "email_templates",
                        "elements" => [
                            [
                                "title" => "Default Welcome Email Template",
                                "path" => "welcome_email",
                                "type" => "select",
                                "provider" => "Modules\EmailTemplate\Entities\WelcomeTemplate",
                                "pluck" => [ "name", "id" ],
                                "default" => 3,
                                "options" => [],
                                "rules" => "exists:email_templates,id",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Confirmation Email Template",
                                "path" => "confirmed_email",
                                "type" => "select",
                                "provider" => "Modules\EmailTemplate\Entities\ConfirmEmailTemplate",
                                "pluck" => [ "name", "id" ],
                                "default" => 5,
                                "options" => [],
                                "rules" => "exists:email_templates,id",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "New Account Template",
                                "path" => "new_account",
                                "type" => "select",
                                "provider" => "Modules\EmailTemplate\Entities\NewAccountTemplate",
                                "pluck" => [ "name", "id" ],
                                "default" => 4,
                                "options" => [],
                                "rules" => "exists:email_templates,id",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Forgot Password Email Template",
                                "path" => "forgot_password",
                                "type" => "select",
                                "provider" => "Modules\EmailTemplate\Entities\ForgotPasswordTemplate",
                                "pluck" => [ "name", "id" ],
                                "default" => 6,
                                "options" => [],
                                "rules" => "exists:email_templates,id",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ],
                            [
                                "title" => "Reset Password Email Template",
                                "path" => "reset_password",
                                "type" => "select",
                                "provider" => "Modules\EmailTemplate\Entities\ResetPasswordTemplate",
                                "pluck" => [ "name", "id" ],
                                "default" => 7,
                                "options" => [],
                                "rules" => "exists:email_templates,id",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name"
                            ]
                        ]
                    ]
                ]
            ]

        ]
    ]
];
?>
