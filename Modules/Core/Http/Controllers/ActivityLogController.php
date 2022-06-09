<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Entities\ActivityLog;
use Modules\Core\Repositories\ActivityLogRepository;
use Modules\Core\Transformers\ActivityLogResource;

class ActivityLogController extends BaseController
{
    private $repository;

    public function __construct(ActivityLog $activityLog, ActivityLogRepository $activityLogRepository)
    {
        $this->model = $activityLog;
        $this->model_name = "Activity Log";
        $this->repository = $activityLogRepository;

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return ActivityLogResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ActivityLogResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, ["subject", "causer"]);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, ["subject", "causer"]);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function destroy(int $id): JsonResponse
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

    public function bulkDelete(Request $request): JsonResponse
    {
        try
        {
            $this->repository->bulkDelete($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }
}
