<?php

namespace Modules\Erp\Jobs\Mapper;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Entities\ErpImportDetail;
use Modules\Erp\Traits\HasErpValueMapper;

class ErpMigrateAttributeOptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasErpValueMapper;

    public function __construct()
    {

    }

    public function handle(): void
    {
        try
        {
            $erp_details = ErpImportDetail::whereErpImportId(1)
                ->get()
                ->chunk(100);

            foreach ($erp_details as $chunk) {
                foreach ($chunk as $detail) {
                    if ($detail->value["colorDescription"] == "SAMPLE") {
                        continue;
                    }
                    $this->createOption($detail);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
