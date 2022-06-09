<?php

return [
    "catalog" => [
        "title" => "Catalog",
        "position" => 3,
        "children" => [
            [
                "title" => "Catalog",
                "slug" => "catalog",
                "subChildren" => [
                    [
                        "title" => "Catalog Inventory",
                        "slug" => "catalog_inventory",
                        "elements" => [
                            [
                                "title" => "Manage Catalog Inventory",
                                "path" => "catalog_inventory_manage",
                                "type" => "select",
                                "provider" => "",
                                "pluck" => [],
                                "default" => 0,
                                "options" => [
                                    [ "value" => 1, "label" => "Yes" ],
                                    [ "value" => 0, "label" => "No" ],
                                ],
                                "rules" => "boolean",
                                "multiple" => false,
                                "scope" => "website",
                                "is_required" => 1,
                                "sort_by" => "",
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];