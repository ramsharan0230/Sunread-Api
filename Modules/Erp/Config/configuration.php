<?php
return [
    "erp" => [
        "title" => "ERP",
        "position" => 4,
        "children" => [
            [
                "title" => "ERP",
                "slug" => "ERP",
                "subChildren" => [
                    [
                        "title" => "Product Import",
                        "slug" => "product_import",
                        "elements" => [
                            [
                                "title" => "Adjust Price",
                                "path" => "adjust_price",
                                "type" => "select",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "no",
                                "options" => [
                                    [ "value" => "yes", "label" => "Yes" ],
                                    [ "value" => "no", "label" => "No" ]
                                ],
                                "rules" => "in:yes,no",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 0,
                                "sort_by" => "",
                            ],
                            [
                                "title" => "Adjustment Type",
                                "path" => "adjustment_type",
                                "type" => "select",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "add",
                                "options" => [
                                    [ "value" => "add", "label" => "Add" ],
                                    [ "value" => "deduct", "label" => "Deduct" ]
                                ],
                                "rules" => "in:add,deduct",
                                "multiple" => false,
                                "scope" => "channel",
                                "is_required" => 0,
                                "sort_by" => "",
                            ],
                            [
                                "title" => "Adjustment Rate",
                                "path" => "adjustment_rate",
                                "type" => "number",
                                "provider" => "",
                                "pluck" => [],
                                "default" => "25",
                                "options" => [],
                                "rules" => "numeric",
                                "scope" => "channel",
                                "is_required" => 0,
                                "sort_by" => "",
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];