<?php

namespace Modules\Core\Repositories\StoreFront;

use Modules\Core\Entities\Website;
use Modules\Core\Repositories\BaseRepository;

class WebsiteRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Website();
        $this->model_key = "website";
    }
}

