<?php

namespace Modules\Erp\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Entities\ErpImport;
use Modules\Erp\Traits\FilterImport\ImportHelper;
use Modules\Erp\Traits\HasErpMapper;
use Modules\Erp\Traits\Mapper\MapperHelper;
use Symfony\Component\HttpFoundation\Response;

class ErpProductDescription implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasErpMapper;
    use ImportHelper;
    use MapperHelper;

    public $tries = 5;
    public $timeout = 90000;

    protected string $sku;
    public string $base_url;

    public function __construct(string $sku)
    {
        $this->sku = $sku;
        $this->base_url = config("erp_config.end_point");
    }

    public function handle(): void
    {
        try
        {
            $erp_import_id = ErpImport::whereType("productDescriptions")->first()->id;
            $data = $this->getErpDescriptionData($this->sku);
            $this->storeErpImport($erp_import_id, $data);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
