<?php

namespace Modules\Erp\Jobs\Mapper;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Entities\ErpImportDetail;
use Modules\Erp\Traits\HasErpValueMapper;

class ErpMigrateProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasErpValueMapper;

    public $tries = 10;
    public $timeout = 90000;

    public function __construct()
    {
    }

    public function handle(): void
    {
        // 1911760 2011850 2131155 2131442  2211708
        // $skus = [
        //     "1911760",
        //     "2011850",
        //     "2131155",
        //     "2131442",
        //     "2211708",
        // ];
        // $erp_import_details = ErpImportDetail::whereErpImportId(2)
        //     ->whereIn("sku", $skus)
        //     ->get();

        // foreach ($erp_import_details as $detail) {
        //     if ($detail->value["webAssortmentColor_Description"] == "SAMPLE") {
        //         continue;
        //     }
        //     if ($detail->value["webAssortmentWeb_Active"] == false ) {
        //         continue;
        //     }
        //     if ($detail->value["webAssortmentWeb_Setup"] != "SR") {
        //         continue;
        //     }
        //     ErpMigratorJob::dispatch($detail)->onQueue("erp");
        // }
        $this->importAll();
    }
}
