<?php

return [

    "services" => [
        'maxmind_database' => [
            'database_path' => storage_path('app/GeoIP/GeoLite2-City.mmdb'),
            'update_url' => sprintf('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz', env('MAXMIND_LICENSE_KEY')),
            'locales' => ['en'],
        ],
    ]
];
