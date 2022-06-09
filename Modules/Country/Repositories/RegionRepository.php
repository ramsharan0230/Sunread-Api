<?php

namespace Modules\Country\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Country\Entities\Region;

class RegionRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Region();
        $this->model_key = "region";
    }
}
