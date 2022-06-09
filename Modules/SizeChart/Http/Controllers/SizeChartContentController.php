<?php

namespace Modules\SizeChart\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\SizeChart\Entities\SizeChartContent;
use Modules\SizeChart\Repositories\SizeChartRepository;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Modules\SizeChart\Repositories\SizeChartContentRepository;
use Modules\SizeChart\Transformers\SizeChartContentResource;

class SizeChartContentController extends BaseController
{
    protected $repository;
    protected $sizeChartContentRepository;

    public function __construct(
        SizeChartContent $sizeChartContent,
        SizeChartContentRepository $sizeChartContentRepository,
        SizeChartRepository $sizeChartRepository
    )
    {
        $this->model = $sizeChartContent;
        $this->model_name = "Size Chart Content";
        $this->sizeChartRepository = $sizeChartRepository;
        $this->repository = $sizeChartContentRepository;

        parent::__construct($this->model, $this->model_name);

        $this->transformer = new SizeChartContentResource(array());
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, [ "size_chart" ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->collection($fetched),
            message: $this->lang("fetch-list-success"),
        );
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback: function ($request) {
                return [
                    "slug" => $this->model->createSlug($request->title),
                    "content" => json_encode($request->content),
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
            message: $this->lang("create-success"),
            response_code: Response::HTTP_CREATED
        );
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id);
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
                return [
                    "slug" => Str::slug($request->title),
                    "content" => $this->is_json($request->content)?$request->content:json_encode($request->content),
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
            message: $this->lang("update-success")
        );
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

        return $this->successResponseWithMessage(
            message: $this->lang("delete-success")
        );
    }

    function is_json($str): bool
    {
        return json_decode($str) != null;
    }
}
