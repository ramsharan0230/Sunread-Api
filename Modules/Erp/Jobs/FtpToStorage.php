<?php

namespace Modules\Erp\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Traits\HasStorageMapper;

class FtpToStorage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasStorageMapper;

    public string $location;

    public function __construct(string $location)
    {
        $this->location = $location;
    }

    public function handle()
    {
        $this->transferFtpToLocal($this->location);
    }

}
