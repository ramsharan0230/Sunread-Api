<?php

namespace Modules\Tax\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Tax\Entities\ProductTaxGroup;
use Modules\Tax\Exceptions\ProductTaxGroupCanNotBeDeleted;
use Modules\Tax\Transformers\ProductTaxGroupResource;
use Modules\Tax\Repositories\ProductTaxGroupRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductTaxGroupController extends BaseController
{
    protected $repository;

    public function __construct(ProductTaxGroup $productTaxGroup, ProductTaxGroupRepository $productTaxGroupRepository)
    {
        $this->repository = $productTaxGroupRepository;
        $this->model = $productTaxGroup;
        $this->model_name = "Product Tax Group";

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return ProductTaxGroupResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ProductTaxGroupResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function all(Request $request): JsonResponse
    {
        try
        {
            $request->without_pagination = true;
            $fetched = $this->repository->fetchAll($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request);
            $created = $this->repository->create($data);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request);
            $updated = $this->repository->update($data, $id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('update-success'));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id, function ($deleted) {
                $product_count = $this->repository->checkTaxOnProduct($deleted->id);
                $tax_rule_count = $deleted->tax_rules->count();
                if(($product_count > 0) || ($tax_rule_count > 0)) throw new ProductTaxGroupCanNotBeDeleted($this->lang("response.delete-failed", [ "name" => $this->model_name ]));
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }
}
