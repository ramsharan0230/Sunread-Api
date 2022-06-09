<?php

return [
    "client" => [
        "hosts" => [
            [
                'host' => env("ELASTIC_HOST", "localhost"),
                'port' => env("ELASTIC_PORT", "9200"),
                'user' => env("ELASTIC_USERNAME"),
                'pass' => env("ELASTIC_PASSWORD"),
            ],
        ],
    ],
    "prefix" => env("ELASTIC_PREFIX", "sail_racing_store_")
];
