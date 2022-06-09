<?php

namespace Modules\UrlRewrite\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\UrlRewrite\Entities\UrlRewrite;
use Modules\UrlRewrite\Repositories\UrlRewriteMainRepository;
use Modules\UrlRewrite\Transformers\UrlRewriteResource;

class UrlRewriteController extends BaseController
{
    protected $repository;

    public function __construct(UrlRewrite $urlRewrite, UrlRewriteMainRepository $urlRewriteRepository)
    {
        $this->model = $urlRewrite;
        $this->model_name = "Url Rewrite";
        $this->repository = $urlRewriteRepository;
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return UrlRewriteResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new UrlRewriteResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $this->validateListFiltering($request);
            $fetched = $this->getFilteredList($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang("fetch-list-success"));
    }

    public function store(Request $request)
    {
        try
        {
            $validated_data = $this->repository->validateUrlRewrite($request);
            $urlRewrite = new Request($this->repository->geturlRewriteData($validated_data));

            $data = $this->repository->validateData($urlRewrite);
            $created = $this->repository->create($data);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang("create-success"), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->model->findOrFail($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }
        return $this->successResponse($this->resource($fetched), $this->lang("fetch-success"));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            UrlRewrite::findOrFail($id);
            $validated_data = $this->repository->validateUrlRewrite($request);
            $urlRewrite = new Request($this->repository->geturlRewriteData($validated_data, $id));

            $data = $this->repository->validateData($urlRewrite);
            $updated = $this->repository->update($data, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("update-success"));
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

        return $this->successResponseWithMessage($this->lang("delete-success"));
    }
}
