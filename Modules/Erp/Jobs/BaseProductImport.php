<?php

namespace Modules\Erp\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Traits\FilterImport\ImportHelper;
use Modules\Erp\Traits\HasErpMapper;

class BaseProductImport implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ImportHelper;
    use HasErpMapper;

    public $tries = 5;
    public $timeout = 90000;

    public $skip_token;

    public function __construct(?string $skip_token = null)
    {
        $this->skip_token = $skip_token;
    }

    public function handle(): void
    {
        $this->erpImport("listProducts", "{$this->url}webItems", $this->skip_token);
    }
}
