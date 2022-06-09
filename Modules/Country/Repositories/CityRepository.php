<?php

namespace Modules\Country\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Country\Entities\City;

class CityRepository extends BaseRepository
{
    public function __construct(City $city)
    {
        $this->model = $city;
        $this->model_key = "city";
    }
}
