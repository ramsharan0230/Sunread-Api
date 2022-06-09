<?php

namespace Modules\Core\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ConfigurationCache implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    
    public array $configurationData;

    public function __construct(array $configurationData)
    {
        $this->configurationData = $configurationData;
    }

   
    public function handle(): void
    {
        try
        {
            $cache_names = [ "taxCache-product", "product_details" ];
            foreach ($cache_names as $cache_name) {
                $cache_key = Redis::keys("{$cache_name}*");
                if ( count($cache_key) > 0 ) {
                    Redis::del($cache_key);
                }
            }

            foreach ($this->configurationData as $config) {
            if (Redis::exists("configuration_data_{$config->scope}_{$config->scope_id}_{$config->path}")) {
                Redis::del("configuration_data_{$config->scope}_{$config->scope_id}_{$config->path}");
            }
            Redis::set("configuration_data_{$config->scope}_{$config->scope_id}_{$config->path}", serialize($config->value));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
