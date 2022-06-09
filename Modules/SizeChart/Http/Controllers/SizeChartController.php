<?php

namespace Modules\SizeChart\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\SizeChart\Repositories\SizeChartScopeRepository;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Repositories\SizeChartRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;
use Modules\SizeChart\Repositories\SizeChartContentRepository;
use Modules\SizeChart\Transformers\SizeChartResource;

class SizeChartController extends BaseController
{
    protected $repository;
    protected $sizeChartScopeRepository;
    protected $sizeChartContentRepository;

    public function __construct(
        SizeChart $sizeChart,
        SizeChartRepository $sizeChartRepository,
        SizeChartScopeRepository $sizeChartScopeRepository,
        SizeChartContentRepository $sizeChartContentRepository,
    ) {
        $this->model = $sizeChart;
        $this->model_name = "Size Chart";
        $this->repository = $sizeChartRepository;
        $this->sizeChartScopeRepository = $sizeChartScopeRepository;
        $this->sizeChartContentRepository = $sizeChartContentRepository;
        parent::__construct($this->model, $this->model_name);

        $this->transformer = new SizeChartResource(array());
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, [ "size_chart_scopes", "website", "size_chart_contents" ], function () use ($request) {
                $request->validate([
                    "website_id" => "required|exists:websites,id"
                ]);
                return $this->model->whereWebsiteId($request->website_id);
            });
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

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback:function ($request) {
                return ["slug" => Str::slug($request->slug) ?? $this->model->createSlug($request->title)];
            });
            $this->repository->validateSlug($data);

            $created = $this->repository->create($data, function ($created) use ($data) {
                if (isset($data["stores"])) {
                    $this->sizeChartScopeRepository->updateOrCreate($data["stores"], $created);
                }
                if(isset($data["attributes"]) && count($data['attributes'])>0){
                    foreach($data['attributes'] as $attribute){
                        $data = $this->sizeChartContentRepository->validateData(new Request($attribute));
                        $this->sizeChartContentRepository->updateOrCreate($data, $created);
                    }
                }
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($created),
            message: $this->lang("create-success"),
            response_code: Response::HTTP_CREATED
        );
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, ["website", "size_chart_scopes", "size_chart_contents"]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($fetched),
            message: $this->lang("fetch-success")
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback: function ($request) {
                return ["slug" => Str::slug($request->slug) ?? $this->model->createSlug($request->title)];
            });
            $this->repository->validateSlug($data, $id);

            $updated = $this->repository->update($data, $id, function ($updated) use ($data) {
                if (isset($data["stores"])) {
                    $this->sizeChartScopeRepository->updateOrCreate($data["stores"], $updated);
                }
                if(isset($data["attributes"]) && count($data['attributes'])>0){
                    foreach($data['attributes'] as $attribute){
                        $data = $this->sizeChartContentRepository->validateData(new Request($attribute));
                        $this->sizeChartContentRepository->updateOrCreate($data, $updated);
                    }
                }
            });
        }
        catch (Exception $exception){
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($updated),
            message: $this->lang("update-success")
        );
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->sizeChartContentRepository->removeSizeChartImage($id);
            $this->repository->delete($id);
        }
        catch (Exception $exception) {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage(
            message: $this->lang("delete-success")
        );
    }
}
