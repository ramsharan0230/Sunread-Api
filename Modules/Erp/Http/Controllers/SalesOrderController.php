<?php

namespace Modules\Erp\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Erp\Jobs\External\ErpSalesOrderJob;
use Modules\Erp\Repositories\ErpSalesOrderRepository;
use Modules\Sales\Entities\Order;
use Modules\Sales\Repositories\StoreFront\OrderRepository;

class SalesOrderController extends BaseController
{
    protected $repository;

    public function __construct()
    {
       $this->model = new Order();
       $this->model_name = "Order";
       $this->repository = new OrderRepository();

       parent::__construct($this->model, $this->model_name);
    }

    public function initalizeWebhook(int $id): JsonResponse
    {
        try
        {
            $order = $this->repository->fetch($id);
            if ($order->status == "processing" && !$order->external_erp_id) {
                ErpSalesOrderJob::dispatch($order)->onQueue("erp");
            }
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage(
            message: $this->lang("erp.processed-success", ["name" => "Order webhook"])
        );
    }
}
