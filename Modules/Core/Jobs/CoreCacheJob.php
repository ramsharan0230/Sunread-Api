<?php

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Core\Facades\CoreCache;

class CoreCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $method, $data;

    public function __construct(string $method, object $data)
    {
        $this->method = $method;
        $this->data = $data;
    }

    public function handle(): void
    {
        $function = $this->method;
        CoreCache::$function($this->data);
    }
}
