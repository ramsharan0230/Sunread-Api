<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\Entities\Feature;
use Modules\Product\Repositories\FeatureRepository;
use Modules\Product\Repositories\FeatureTranslationRepository;
use Modules\Product\Transformers\FeatureResource;
use Exception;

class FeatureController extends BaseController
{
    private $repository;
    private $featureTranslationRepository;

    public function __construct(
        Feature $feature,
        FeatureRepository $featureRepository,
        FeatureTranslationRepository $featureTranslationRepository
    ) {
        $this->model = $feature;
        $this->model_name = "Feature";
        $this->repository = $featureRepository;
        parent::__construct($this->model, $this->model_name);
        $this->featureTranslationRepository = $featureTranslationRepository;
    }

    public function collection(object $data): ResourceCollection
    {
        return FeatureResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new FeatureResource($data);
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

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, [
                "image" => "required|mimes:bmp,jpeg,jpg,png,webp,svg"
            ]);
            $data["image"] = $this->storeImage($request, "image", strtolower($this->model_name));

            $created = $this->repository->create($data, function($created) use($request) {
                if ($request->translations) {
                    $this->featureTranslationRepository->updateOrCreate($request->translations, $created);
                }
            });
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
            $fetched = $this->repository->show($id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateFeatureData($request);

            if ($request->file("image")) {
                $data["image"] = $this->storeImage($request, "image", strtolower($this->model_name));
            } else {
                $data = $this->repository->updateImage($request, $data, $id);
            }

            $updated = $this->repository->update($data, $id, function($updated) use($request) {
                if ($request->translations) {
                    $this->featureTranslationRepository->updateOrCreate($request->translations, $updated);
                }
            });
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
                $this->repository->removeImage($deleted);
            });
        }
        catch( Exception $exception )
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
