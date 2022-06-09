<?php

namespace Modules\Erp\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Bus;
use Modules\Erp\Entities\ErpImport;
use Modules\Erp\Jobs\FilterImport\ErpDataImporrt;
use Modules\Erp\Traits\FilterImport\ImportHelper;
use Modules\Erp\Traits\HasErpMapper;
use Symfony\Component\HttpFoundation\Response;

class ImportProductFilteredAttribute implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasErpMapper;
    use ImportHelper;

    public $tries = 5;
    public $timeout = 90000;

    public ?string $sku;

    public array $import_api_data;

    public function __construct(?string $sku = null)
    {
        $this->sku = $sku;
        $this->import_api_data = [
            "salePrices" => "webSalesPrices",
            "attributeGroups" => "webItemAttributeGroups",
            "eanCodes" => "webItemCrossReferences",
            "productVariants" => "webItemVariants",
            "webAssortments" => "webAssortments",
            "webInventories" => "webInventorys",
        ];
    }

    public function handle(): void
    {
        try
        {
            $batch = Bus::batch([])->onQueue("erp")->dispatch();
            foreach ($this->import_api_data as $type => $api) {
                $response = $this->basicAuth()->get("{$this->url}{$api}", $this->filterToken($type, $this->sku));

                if ($response->status() == Response::HTTP_OK) {
                    $response = $response->json()["value"];
                    $erp_import_id = ErpImport::whereType($type)->first()?->id;
                    if (!$erp_import_id) {
                        throw new Exception("Invalid Type {$type}");
                    }
                    $batch->add(new ErpDataImporrt($erp_import_id, $response) );
                }
            }

        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
