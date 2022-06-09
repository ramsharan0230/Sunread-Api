<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Core\Entities\CacheManagement;
use Modules\Core\Repositories\CacheManagementRepository;
use Modules\Core\Transformers\CacheManagementResource;
use Symfony\Component\HttpFoundation\Response;

class CacheManagementController extends BaseController
{
    private $repository;

    public function __construct()
    {
        $this->model = new CacheManagement();
        $this->model_name = "Cache Management";

        $this->repository = new CacheManagementRepository();
        $this->transformer = new CacheManagementResource(array());
        parent::__construct($this->model, $this->model_name);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->collection($fetched),
            message: $this->lang("fetch-list-success")
        );
    }

    public function clearCache(Request $request): JsonResponse
    {
        try
        {
           $this->repository->clearCustomCache($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage(
            message: $this->lang("response.clear-success", [ "name" => "Cache" ])
        );
    }

    public function clearAllCache(): JsonResponse
    {
        try
        {
            if (count(Redis::keys("*")) > 0) {
                Redis::del(Redis::keys("*"));
            }
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage(
            message: $this->lang("response.clear-success", [ "name" => "All Cache" ])
        );
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback: function ($request) {
                return [
                    "key" => $this->model->createSlug($request->key),
                    "slug" => $this->model->createSlug($request->name),
                ];
            });

            $created = $this->repository->create($data);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($created),
            message: $this->lang('create-success'),
            response_code: Response::HTTP_CREATED
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback: function ($request) {
                return [
                    "key" => $this->model->createSlug($request->key),
                    "slug" => $this->model->createSlug($request->name),
                ];
            });

            $updated = $this->repository->update($data, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($updated),
            message: $this->lang('update-success')
        );
    }
}
