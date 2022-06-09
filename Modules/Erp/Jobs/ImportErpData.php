<?php

namespace Modules\Erp\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Modules\Erp\Entities\ErpImport;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Erp\Entities\ErpImportDetail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Modules\Erp\Traits\FilterImport\ImportHelper;

class ImportErpData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ImportHelper;

    public $tries = 5;
    public $timeout = 90000;

    public array $erp_data;
    public string $type;

    public function __construct(string $type, array $erp_data)
    {
        $this->type = $type;
        $this->erp_data = $erp_data;
    }

    public function handle(): void
    {
        try
        {
            $erp_import_id = ErpImport::whereType($this->type)->first()->id;

            if (!$erp_import_id) {
                throw new Exception("Invalid Type {$this->type}");
            }

            $chunked = array_chunk($this->erp_data, 100);
            $jobs = [];
            foreach ($chunked as $chunk) {
                $this->storeErpImport($erp_import_id, $chunk, function ($sku) use (&$jobs) {
                    if ($this->type == "listProducts") {
                        $jobs = array_merge($jobs, [
                            new ImportProductFilteredAttribute($sku),
                            new ErpProductDescription($sku),
                        ]);
                    } else {
                        $jobs = [];
                    }
                });
            }
            Bus::chain($jobs)->onQueue("erp")->dispatch();

        }
        catch (Exception $exception)
        {
            Log::error(json_encode($exception));
            throw $exception;
        }
    }
}
