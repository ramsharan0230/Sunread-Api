<?php

namespace Modules\Erp\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Erp\Entities\ErpWebhookLog;
use Modules\Erp\Jobs\Webhook\ErpProductImport;
use Modules\Erp\Repositories\ErpProductRepository;
use Modules\Erp\Repositories\ErpWebhookLogRepository;
use Modules\Erp\Rules\CheckConfigurableProduct;
use Modules\Erp\Rules\ConfigurableSkuRule;
use Modules\Product\Entities\Product;

class ErpProductController extends BaseController
{
    protected $repository;
    protected $erpWebhookRepository;

    public function __construct()
    {
       $this->model = new ErpWebhookLog();
       $this->model_name = "Product";
       $this->erpWebhookRepository = new ErpWebhookLogRepository();
       $this->repository = new ErpProductRepository();
       parent::__construct($this->model, $this->model_name);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        try
        {
            $request->merge(["website_id" => $id]);
            $data = $this->repository->validateData($request, callback: function ($request) {
                return ($request->type == "create")
                    ? $request->validate(["sku" => "required|unique:products,sku"])
                    : $request->validate(["sku" => "required|exists:products,sku"]);
            });

            ErpProductImport::dispatchSync($data);
            Log::channel("erp-webhook-log")
                ->info("erp-webhook-log-{$request->type}", [
                    "context" => $data
                ]);
        }
        catch (Exception $exception)
        {
            Log::channel("erp-webhook-log")
                ->error($exception->getMessage(), [
                    "context" => [
                        "request" => $request->all(),
                        "response" => (get_class($exception) == ValidationException::class)
                            ? $exception->errors()
                            : $exception->getMessage()
                    ]
                ]);
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage(
            message: $this->lang("erp.{$request->type}-success")
        );
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "sku" => [
                    "array",
                    "required",
                    new ConfigurableSkuRule(),
                ],
                "sku.*" => "required|exists:products,sku",
            ]);
            $this->repository->bulkUpdate($request->sku);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage(
            message: $this->lang("erp.processed-success", ["name" => "Product Update"])
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $request->merge(["id" => $id]);
            $data = $this->repository->validateData($request, [
                "id" => [
                    "required",
                    "exists:products,id",
                    new CheckConfigurableProduct(),
                ],
                "type" => "required|in:update,inventory,price",
            ], function ($request) use ($id) {
                return [
                    "sku" => $this->repository->fetch($id)->sku,
                ];
            });
            unset($data["id"]);
            ErpProductImport::dispatchSync($data);
            Log::channel("erp-webhook-log")
                ->info("erp-webhook-log-{$request->type}", [
                    "context" => $data
                ]);
        }
        catch (Exception $exception)
        {
            Log::channel("erp-webhook-log")
                ->error($exception->getMessage(), [
                    "context" => [
                        "request" => $request->all(),
                        "response" => (get_class($exception) == ValidationException::class)
                            ? $exception->errors()
                            : $exception->getMessage()
                    ]
                ]);
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage(
            message: $this->lang("erp.{$request->type}-success")
        );
    }
}
