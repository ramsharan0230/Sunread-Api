<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Entities\FailedJob;
use Modules\Core\Transformers\FailedJobResource;

class FailedJobController extends BaseController
{
    protected $model, $model_name;

    public function __construct(FailedJob $failedJob)
    {
        $this->model = $failedJob;
        $this->model_name = "FailedJob";
    }

    public function collection(object $data): ResourceCollection
    {
        return FailedJobResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new FailedJobResource($data);
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
}
