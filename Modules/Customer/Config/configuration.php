<?php

return [
    "guest" => [
        "title" => "Guest",
        "position" => 5,
        "children" => [
            [
                "title" => "Email Template Settings",
                "slug" => "email_template_settings",
                "subChildren" => [
                    [
                        "title" => "Guest Email Templates",
                        "slug" => "guest_email_templates",
                        "elements" => [
                            [
                                "title" => "Guest Registration Template",
                                "path" => "guest_registration", // template code
                                "type" => "select",
                                "provider" => "Modules\Customer\Entities\GuestRegistrationTemplate",
                                "pluck" => [ "name", "id" ],
                                "default" => "12",
                                "options" => [],
                                "rules" => "exists:email_templates,id",
                                "multiple" => false,
                                "scope" => "store",
                                "is_required" => 1,
                                "sort_by" => "name",
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
