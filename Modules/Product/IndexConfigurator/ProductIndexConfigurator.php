<?php

namespace Modules\Product\IndexConfigurator;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class ProductIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    protected $name = 'sailracing';
    /**
     * @var array
     */
    protected $settings = [
        "index.mapping.total_fields.limit" => 100000000,
        "number_of_shards"=> 5,
        "number_of_replicas"=> 2
    ];
}