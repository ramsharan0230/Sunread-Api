<?php

namespace Modules\Erp\Repositories;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\ValidationException;
use Modules\Core\Repositories\BaseRepository;
use Modules\Erp\Facades\ErpLog;
use Modules\Erp\Jobs\Mapper\ErpMigratorJob;
use Modules\Erp\Jobs\Webhook\ErpAttributeOptionImport;
use Modules\Erp\Jobs\Webhook\ErpProductUpdate;
use Modules\Erp\Traits\Mapper\MapperHelper;
use Modules\Erp\Traits\Mapper\PriceMapper;
use Modules\Erp\Traits\Webhook\HasErpWebhookHelper;
use Modules\Product\Entities\Product;
use Symfony\Component\HttpFoundation\Response;

class ErpProductRepository extends BaseRepository
{
    use HasErpWebhookHelper;
    use MapperHelper;
    use PriceMapper;

    public string $base_url;

    public function __construct()
    {
        $this->model = new Product();
        $this->model_name = "Product";
        $this->model_key = "Product";
        $this->base_url = config("erp_config.end_point");
        $this->rules = [
            "website_id" => "required|exists:websites,id",
            "type" => "required|in:create,update,inventory,price",
        ];
    }

    public function createProduct(array $data): void
    {
        try
        {
            $response = $this->httpGet("webItems?\$filter=no eq '{$data['sku']}'");
            if ($response->status() == Response::HTTP_OK) {
                $response = $response->json();
                if (!empty($response["value"])) {
                    unset($response["@odata.context"]);
                    $response = Arr::first($response["value"]);
                    $data = (object) array_merge($data, [
                        "value" => $response,
                    ]);

                    ErpLog::webhookLog(
                        website_id: $data->website_id,
                        entity_type: $data->type,
                        entity_id: $data->sku,
                        payload: $response,
                    );
                    Bus::chain([
                        new ErpAttributeOptionImport($data),
                        new ErpMigratorJob($data, true),
                    ])->onQueue("erp")->dispatch();
                } else {
                    throw ValidationException::withMessages(["sku" => "Sku not found on erp."]);
                }
            } else {
                ErpLog::webhookLog(
                    website_id: $data["website_id"],
                    entity_type: $data["type"],
                    entity_id: $data["sku"],
                    payload: $response,
                    is_processing: 0,
                    status: 0
                );
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function bulkUpdate(array $skus): void
    {
        try
        {
            foreach ($skus as $sku) {
                // added product update attribute jobs here.
                ErpProductUpdate::dispatch(["sku" => $sku])->onQueue("erp");
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
