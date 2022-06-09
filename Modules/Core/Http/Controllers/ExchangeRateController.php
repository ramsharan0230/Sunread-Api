<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Entities\ExchangeRate;
use Modules\Core\Repositories\ExchangeRateRepository;
use Modules\Core\Transformers\ExchangeRateResource;

class ExchangeRateController extends BaseController
{
    protected $repository;

    public function __construct(ExchangeRate $exchangeRate, ExchangeRateRepository $exchangeRateRepository)
    {
        $this->model = $exchangeRate;
        $this->model_name = "Exchange Rate";
        $this->repository = $exchangeRateRepository;

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return ExchangeRateResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ExchangeRateResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, ["source", "target"]);
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
            $fetched = $this->repository->fetch($id, ["source", "target"]);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function update(Request $request, $id): JsonResponse
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

    public function destroy($id): JsonResponse
    {
        try
        {
            $this->repository->delete($id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }
}
