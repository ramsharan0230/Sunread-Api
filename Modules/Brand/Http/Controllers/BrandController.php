<?php

namespace Modules\Brand\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Entities\Brand;
use Modules\Brand\Repositories\BrandRepository;
use Modules\Brand\Transformers\BrandResource;
use Modules\Core\Http\Controllers\BaseController;

class BrandController extends BaseController
{
    protected $repository;

    public function __construct(Brand $brand, BrandRepository $brandRepository)
    {
        $this->model = $brand;
        $this->model_name = "Brand";
        $this->repository = $brandRepository;
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return BrandResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new BrandResource($data);
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

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request);
            $data["image"] = $this->storeImage($request, "image", strtolower($this->model_name));
            $data["slug"] = $data["slug"] ?? $this->model->createSlug($request->name);
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
            $data = $this->repository->validateData($request,[
                "slug" => "nullable|unique:brands,slug,{$id}",
                "image" => "sometimes|nullable|mimes:png,jpg,bmp,jpg,jpeg"
            ]);

            if ($request->file("image"))
            {
                $data["image"] = $this->storeImage($request, "image", strtolower($this->model_name));
            }
            else
            {
                unset($data["image"]);
            }

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
            $this->repository->delete($id, function ($deleted) {
                if ($deleted->image) Storage::delete($deleted->image);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang("delete-success"));
    }
}
