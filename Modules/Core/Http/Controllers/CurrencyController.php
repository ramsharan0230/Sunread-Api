<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Core\Entities\Currency;
use Modules\Core\Transformers\CurrencyResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Entities\Store;
use Modules\Core\Repositories\CurrencyRepository;
use Exception;

class CurrencyController extends BaseController
{
    protected $repository;

    public function __construct(Currency $currency, CurrencyRepository $currencyRepository)
    {
        $this->model = $currency;
        $this->model_name = "Currency";
        $this->repository = $currencyRepository;
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return CurrencyResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CurrencyResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $this->validateListFiltering($request);
            $currencies = $this->getFilteredList($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($currencies), $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request);
            $currency = $this->repository->create($data);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($currency), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $currency = $this->model->findOrFail($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($currency), $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request,[
                "code" => "required|min:3|max:3|unique:currencies,code,{$id}"
            ]);

            $currency = $this->repository->update($data, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($currency), $this->lang('update-success'));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            // Cannot Delete if only one currency
            if ($this->model->count() == 1) return $this->errorResponse($this->lang('last-delete-error'));
            $this->repository->delete($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try
        {
            $updated = $this->repository->updateStatus($request, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("status-updated"));
    }
}
