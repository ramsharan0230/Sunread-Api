<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Customer\Entities\CustomerGroup;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Exceptions\DefaultGroupCannotUpdateException;
use Modules\Customer\Transformers\CustomerGroupResource;
use Modules\Customer\Repositories\CustomerGroupRepository;
use Exception;

class CustomerGroupController extends BaseController
{
    protected $repository;

    public function __construct(CustomerGroupRepository $customerGroupRepository, CustomerGroup $customer_group)
    {
        $this->repository = $customerGroupRepository;
        $this->model = $customer_group;
        $this->model_name = "Customer Group";

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return CustomerGroupResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CustomerGroupResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, [ "tax_group" ]);
        }
        catch (Exception $exception)
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
            if ( $request->slug == null ) $data["slug"] = $this->model->createSlug($request->name);
            $created = $this->repository->create($data);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, [ "tax_group" ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
//            if($id == 1) throw new DefaultGroupCannotUpdateException(__("core::app.response.cannot-update-default", [ "name" => $this->model_name ]));
            $data = $this->repository->validateData($request, [
                "slug" => "nullable|unique:customer_groups,slug,{$id}"
            ]);
            if ( $request->slug == null ) $data["slug"] = $this->model->createSlug($request->name);
            $updated = $this->repository->update($data, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('update-success'));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }
}
