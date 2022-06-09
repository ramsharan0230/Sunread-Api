<?php

namespace Modules\Inventory\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Entities\CatalogInventoryItem;
use Modules\Inventory\Repositories\CatalogInventoryItemRepository;
use Modules\Inventory\Transformers\CatalogInventoryItemResource;

class CatalogInventoryItemController extends BaseController
{
    protected $repository;

    public function __construct(CatalogInventoryItem $catalogInventoryItem, CatalogInventoryItemRepository $catalogInventoryItemRepository)
    {
        $this->model = $catalogInventoryItem;
        $this->model_name = "Catalog Inventory Item";
        $this->repository = $catalogInventoryItemRepository;   
        
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return CatalogInventoryItemResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CatalogInventoryItemResource($data);
    }

    public function index(Request $request, int $id): JsonResponse
    {
        try
        {           
            $fetched = $this->repository->fetchAll($request, callback: function () use ($id) {
                return $this->repository->filterByProduct($id);
            });
        }
        catch ( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang("fetch-list-success"));
    }
}
