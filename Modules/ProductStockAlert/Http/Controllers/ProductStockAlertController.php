<?php

namespace Modules\ProductStockAlert\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\ProductStockAlert\Entities\ProductAlertStock;
use Modules\ProductStockAlert\Transformers\ProductStockAlertResource;
use Modules\ProductStockAlert\Repositories\ProductStockAlertRepository;

class ProductStockAlertController extends BaseController
{
    protected $productStockAlertRepository;

    public function __construct(ProductStockAlertRepository $productStockAlertRepository, ProductAlertStock $productAlertStock)
    {
        $this->middleware('validate.website.host');
        $this->middleware('validate.channel.code');
        $this->middleware('validate.store.code');

        $this->model = $productAlertStock;
        $this->model_name = "Product Alert Stock";

        $this->productStockAlertRepository = $productStockAlertRepository;

        parent::__construct($this->model, $this->model_name);
    }
    
    public function createProductStockAlert(Request $request): JsonResponse
    {
        try
        {
            $this->productStockAlertRepository->createProductStockAlert($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang("response.product-stock-alert"));
    }
}
