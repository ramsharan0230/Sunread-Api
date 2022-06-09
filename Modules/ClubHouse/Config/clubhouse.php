<?php
return [
    "attributes" => [
        "general" => [
            "title" => "General Details",
            "elements" => [
                [
                    "title" => "Title",
                    "slug" => "title",
                    "type" => "text",
                    "value" => "",
                    "scope" => "store",
                    "options" => [],
                    "rules" => "string",
                    "is_required" => 1
                ],
                [
                    "title" => "Slug",
                    "slug" => "slug",
                    "type" => "text",
                    "value" => "",
                    "scope" => "website",
                    "options" => [],
                    "rules" => "string",
                    "is_required" => 0
                ],
                [
                    "title" => "Type",
                    "slug" => "type",
                    "type" => "select",
                    "value" => "",
                    "scope" => "website",
                    "options" => [
                        [ "value" => "clubhouse", "label" => "Club House" ],
                        [ "value" => "resort", "label" => "Resort" ]
                    ],
                    "rules" => "string|in:clubhouse,resort",
                    "is_required" => 1
                ],
                [
                    "title" => "Status",
                    "slug" => "status",
                    "type" => "select",
                    "value" => "",
                    "scope" => "website",
                    "options" => [
                        [ "value" => "1", "label" => "Enabled" ],
                        [ "value" => "0", "label" => "Disabled" ]
                    ],
                    "rules" => "in:0,1",
                    "is_required" => 0
                ],
            ]
        ],
        "search_engine_optimization" => [
            "title" => "Search Engine Optimization",
            "elements" => [
                [
                    "title" => "Meta Title",
                    "slug" => "meta_title",
                    "type" => "text",
                    "value" => "",
                    "scope" => "store",
                    "options" => [],
                    "rules" => "",
                    "is_required" => 0
                ],
                [
                    "title" => "Meta KeyWords",
                    "slug" => "meta_keywords",
                    "type" => "text",
                    "value" => "",
                    "scope" => "store",
                    "options" => [],
                    "rules" => "",
                    "is_required" => 0

                ],
                [
                    "title" => "Meta Description",
                    "slug" => "meta_description",
                    "type" => "textarea",
                    "value" => "",
                    "scope" => "store",
                    "options" => [],
                    "rules" => "",
                    "is_required" => 0
                ]
            ]
        ],
        "hero_banner" => [
            "title" => "Hero Banner",
            "elements" => [
                [
                    "title" => "Background Type",
                    "slug" => "background_type",
                    "type" => "select",
                    "value" => "",
                    "scope" => "website",
                    "has_condition" => 1,
                    "options" => [
                        [ "value" => "image", "label" => "Image" ],
                        [ "value" => "video", "label" => "Video" ]
                    ],
                    "rules" => "nullable|in:image,video",
                    "multiple" => false,
                    "is_required" => 0
                ],
                [
                    "title" => "Background Image",
                    "slug" => "background_image",
                    "type" => "file",
                    "value" => "",
                    "scope" => "website",
                    "condition" => [
                        "field" => 'background_type',
                        "value" => 'image'
                    ],
                    "options" => [],
                    "rules" => "mimes:jpeg,jpg,bmp,png,gif,svg|required_if:background_type,image",
                    "is_required" => 0
                ],
                [
                    "title" => "Background Video",
                    "slug" => "background_video",
                    "type" => "text",
                    "condition" => [
                        "field" => 'background_type',
                        "value" => 'video'
                    ],
                    "value" => "",
                    "scope" => "website",
                    "options" => [],
                    "rules" => "string|required_if:background_type,video",
                    "is_required" => 0
                ],
                [
                    "title" => "Subtitle",
                    "slug" => "subtitle",
                    "type" => "text",
                    "value" => "",
                    "scope" => "store",
                    "options" => [],
                    "rules" => "string",
                    "is_required" => 0
                ],
                [
                    "title" => "Hero Content",
                    "slug" => "hero_content",
                    "type" => "textarea",
                    "value" => "",
                    "scope" => "store",
                    "options" => [],
                    "rules" => "string",
                    "is_required" => 0
                ],
            ]
        ],
        "content" => [
            "title" => "Content",
            "elements" => [
                [
                    "title" => "Thumbnail",
                    "slug" => "thumbnail",
                    "type" => "file",
                    "value" => "",
                    "scope" => "website",
                    "options" => [],
                    "rules" => "mimes:jpeg,jpg,bmp,png,svg",
                    "is_required" => 1
                ],
                [
                    "title" => "Opening hours",
                    "slug" => "opening_hours",
                    "type" => "texteditor",
                    "value" => "",
                    "scope" => "store",
                    "options" => [],
                    "rules" => "string",
                    "is_required" => 1
                ],
                [
                    "title" => "Address",
                    "slug" => "address",
                    "type" => "texteditor",
                    "value" => "",
                    "scope" => "store",
                    "options" => [],
                    "rules" => "string",
                    "is_required" => 1
                ],
                [
                    "title" => "Contact",
                    "slug" => "contact",
                    "type" => "texteditor",
                    "value" => "",
                    "scope" => "store",
                    "options" => [],
                    "rules" => "string",
                    "is_required" => 1
                ],
            ]
        ],
        "location" => [
            "title" => "Location",
            "elements" => [
                [
                    "title" => "Latitude",
                    "slug" => "latitude",
                    "type" => "text",
                    "value" => "",
                    "scope" => "website",
                    "options" => [],
                    "rules" => "string",
                    "is_required" => 0
                ],
                [
                    "title" => "Longitude",
                    "slug" => "longitude",
                    "type" => "text",
                    "value" => "",
                    "scope" => "website",
                    "options" => [],
                    "rules" => "string",
                    "is_required" => 0
                ],
            ]
        ],
    ],
    "absolute_path" => [
        "title" => "general.elements.0",
        "slug" => "general.elements.1",
        "type" => "general.elements.2",
        "status" => "general.elements.3",
        "thumbnail" => "content.elements.0",
        "opening_hours" => "content.elements.1",
        "address" => "content.elements.2",
        "contact" => "content.elements.3",
        "latitude" => "location.elements.0",
        "longitude" => "location.elements.1",
        "background_type" => "hero_banner.elements.0",
        "background_image" => "hero_banner.elements.1",
        "background_video" => "hero_banner.elements.2",
        "subtitle" => "hero_banner.elements.3",
        "hero_content" => "hero_banner.elements.4",
        "meta_title" => "search_engine_optimization.elements.0",
        "meta_keywords" => "search_engine_optimization.elements.1",
        "meta_description" => "search_engine_optimization.elements.2"
    ]
];
