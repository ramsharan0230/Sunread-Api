<?php

namespace Modules\Core\Repositories;

use Illuminate\Support\Facades\Redis;
use Modules\Core\Entities\CacheManagement;
use Exception;

class CacheManagementRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new CacheManagement();
        $this->model_key = "core.cache_management";
        $this->rules = [
            "name" => "required",
            "description" => "nullable",
            "tag" => "nullable",
            "key" => "required|unique:cache,key",
        ];
    }

    public function clearCustomCache(object $request): bool
    {
        try
        {
            $request->validate([
                "ids" => "array|required",
                "ids.*" => "required|exists:cache,id",
            ]);

            foreach ($request->ids as $id) {
                $fetch = $this->fetch($id);
                if (count(Redis::keys("{$fetch->key}*")) > 0) {
                    Redis::del(Redis::keys("{$fetch->key}*"));
                }
            }
        }
        catch( Exception $exception )
        {
            throw $exception;
        }

        return true;
    }
}
