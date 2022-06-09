<?php

namespace Modules\Core\Repositories;

use Modules\Core\Entities\Store;
use Modules\Core\Repositories\BaseRepository;

class StoreRepository extends BaseRepository
{

    public function __construct(Store $store)
    {
        $this->model = $store;
        $this->model_name = "Store";
        $this->model_key = "core.stores";
        $this->rules = [
            "name" => "required",
            "position" => "sometimes|numeric",
            "status" => "sometimes|boolean",
            "channel_id" => "required|exists:channels,id",
        ];
        $this->restrict_default_delete = true;
    }

}
