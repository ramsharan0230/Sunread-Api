<?php
namespace Modules\Core\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Configuration;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;

class ConfigurationFactory extends Factory
{
    protected $model = \Modules\Core\Entities\Configuration::class;

    public function definition(): array
    {
        $paths = [ "default_country", "allow_countries", "store_phone_number", "store_hours_operation", "store_country", "store_zip_code", "store_street_address", "store_address_line2", "store_icon", "store_locale", "locale_weight_unit", "global_timezone", "channel_time_zone"];
        $absolute_paths = [ "0.elements.0", "0.elements.1", "1.elements.1", "1.elements.2",  "1.elements.3", "1.elements.5", "1.elements.7", "1.elements.8", "1.elements.9", "2.elements.0", "2.elements.1", "2.elements.2", "2.elements.3"  ];


        $scope = Arr::random([ "website", "channel", "store" ]);

        switch ($scope) {
            case "website":
                $scope_id = Website::factory()->create()->id;
                break;

            case "channel":
                $scope_id = Channel::factory()->create()->id;
                break;

            case "store":
                $scope_id = Store::factory()->create()->id;
                break;
        }

        foreach($paths as $i => $path)
        {
            $items[$path] = [
                'use_default_value' => 1,
                'absolute_path' => "general.children.0.subChildren.{$absolute_paths[$i]}"
            ];
        }
        return [
            "absolute_path" => "general.children.0.subChildren",
            'scope' => $scope,
            'scope_id' => $scope_id,
            'items' => $items
        ];
    }
}
