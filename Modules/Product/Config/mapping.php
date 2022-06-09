<?php
return [
    "properties" =>  [
        "id" =>  [
            "type" => "long"
        ],
        "name" =>  [
            "type" => "text",
            "analyzer" => "keyword",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ],
            "fielddata"=> true
        ],
        "sku" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "price" =>  [
            "type" => "float"
        ],
        "type" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "status" =>  [
            "type" => "long"
        ],
        "product_status" =>  [
            "type" => "long"
        ],
        "quantity"=> [
            "type"=> "text",
            "fields"=> [
                "keyword"=> [
                    "type"=> "keyword",
                    "ignore_above"=> 256
                ]
            ]
        ],
        "website_id" =>  [
            "type" => "long"
        ],
        "parent_id" =>  [
            "type" => "long"
        ],
        "list_status" =>  [
            "type" => "long"
        ],
        "visibility" =>  [
            "type" => "long"
        ],
        "visibility_value" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "is_in_stock"=> [
            "type"=> "long"
        ],
        "base_image" =>  [
            "properties"=> [
                "url" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ]
                    ]
                ],
                "background_color" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ],
                    ],
                ],
                "background_size" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ],
                    ],
                ],
            ],
        ],
        "has_weight" =>  [
            "type" => "long"
        ],
        "has_weight_value" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "new_from_date" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "new_to_date" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "description" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "short_description" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "meta_keywords" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "meta_title" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "meta_description" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "special_price" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "special_from_date" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "special_to_date" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "tax_class_id" =>  [
            "type" => "long"
        ],
        "tax_class_id_value" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "url_key" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "weight" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "small_image"=> [
            "properties"=> [
                "url" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ]
                    ]
                ],
                "background_color" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ]
                    ]
                ]
            ]
        ],
        "thumbnail_image" =>  [
            "properties"=> [
                "url" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ]
                    ]
                ],
                "background_color" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ]
                    ]
                ]
            ]
        ],
        "section_background" =>  [
            "properties"=> [
                "url" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ]
                    ]
                ],
                "background_color" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ]
                    ]
                ]
            ]
        ],
        "gallery" =>  [
            "properties"=> [
                "url" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ]
                    ]
                ],
                "background_color" =>  [
                    "type" => "text",
                    "fields" =>  [
                        "keyword" =>  [
                            "type" => "keyword",
                            "ignore_above" => 256
                        ]
                    ]
                ]
            ]
        ],
        "color" =>  [
            "type" => "long"
        ],
        "color_value" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ],
            "fielddata"=> true
        ],
        "size" =>  [
            "type" => "long"
        ],
        "size_value" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ],
            "fielddata"=> true
        ],
        "configurable_size" =>  [
            "type" => "long"
        ],
        "configurable_size_value" =>  [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ],
            "fielddata"=> true
        ],
        "ean_code" => [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ],
        ],
        "size_and_care" => [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ],
        ],
        "features" => [
            "type" => "text",
            "fields" =>  [
                "keyword" =>  [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ],
        ],
        "categories"=> [
            "properties"=> [
                "id"=> [
                    "type"=> "long"
                ],
                // "name" =>  [
                //     "type" => "text",
                //     "fields" =>  [
                //         "keyword" =>  [
                //             "type" => "keyword",
                //             "ignore_above" => 256
                //         ]
                //     ]
                // ],
                // "slug" =>  [
                //     "type" => "text",
                //     "fields" =>  [
                //         "keyword" =>  [
                //             "type" => "keyword",
                //             "ignore_above" => 256
                //         ]
                //     ]
                // ]
            ]
        ]
    ],

];
