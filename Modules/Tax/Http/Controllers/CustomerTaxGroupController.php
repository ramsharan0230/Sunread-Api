<?php

namespace Modules\Tax\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Tax\Entities\CustomerTaxGroup;
use Modules\Tax\Exceptions\CustomerTaxGroupCanNotBeDeleted;
use Modules\Tax\Transformers\CustomerTaxGroupResource;
use Modules\Tax\Repositories\CustomerTaxGroupRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerTaxGroupController extends BaseController
{
    protected $repository;

    public function __construct(CustomerTaxGroup $customerTaxGroup, CustomerTaxGroupRepository $customerTaxGroupRepository)
    {
        $this->repository = $customerTaxGroupRepository;
        $this->model = $customerTaxGroup;
        $this->model_name = "Customer Tax Group";
        $exception_statuses = [
            CustomerTaxGroupCanNotBeDeleted::class => 403
        ];

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return CustomerTaxGroupResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CustomerTaxGroupResource($data);
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
            $this->repository->delete($id, function ($deleted){
                $tax_rule_count = $deleted->tax_rules->count();
                $tax_group_count = $deleted->tax_group->count();
                if(($tax_group_count > 0) || ($tax_rule_count > 0)) throw new CustomerTaxGroupCanNotBeDeleted($this->lang("response.delete-failed", [ "name" => $this->model_name ]));
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }
}
