<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Entities\Website;
use Modules\Core\Facades\CoreCache;

class RedisClear extends Command
{
    protected $signature = 'redis:clear';

    protected $description = 'This command will clear all redis cache';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): bool
    {
        if(count(Redis::keys("*")) > 0) {
            Redis::del(Redis::keys("*"));
        }

        $websites = Website::all();
        foreach ($websites as $website) {
            CoreCache::createWebsiteCache($website);
            CoreCache::getWebsiteAllChannel($website);
            CoreCache::getWebsiteAllStore($website);
        }

        $this->info("Redis cache refreshed");

        return true;
    }
}
