<?php

namespace Modules\Erp\Jobs\FilterImport;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Traits\FilterImport\ImportHelper;

class ErpDataImporrt implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ImportHelper;

    public array $data;
    public int $erp_import_id;

    public function __construct(int $erp_import_id, array $data)
    {
        $this->erp_import_id = $erp_import_id;
        $this->data = $data;
    }

    public function handle(): void
    {
        $this->storeErpImport($this->erp_import_id, $this->data);
    }
}
