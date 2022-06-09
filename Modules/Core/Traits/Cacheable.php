<?php

namespace Modules\Core\Traits;

use Exception;
use Illuminate\Support\Facades\Redis;

trait Cacheable
{
    public function storeCache(string $key, string $hash, callable $callback): mixed
    {
        try
        {
            $cacheKey = "{$key}_{$hash}";
            $getCache = Redis::get($cacheKey);

            if (!$getCache) {
                $data = $callback();
                Redis::set($cacheKey, json_encode($data));
                $getCache = Redis::get($cacheKey);
            }

            $getCached = json_decode($getCache);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $getCached;
    }

    public function flushAllCache(string $key): void
    {
        try
        {
            $cache_name = "{$key}*";
            $cache_key = Redis::keys($cache_name);

            if (count($cache_key) > 0) {
                Redis::del($cache_key);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
