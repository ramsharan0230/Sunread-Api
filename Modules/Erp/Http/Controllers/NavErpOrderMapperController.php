<?php

namespace Modules\Erp\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Erp\Entities\NavErpOrderMapper;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Erp\Transformers\NavErpOrderMapperResource;
use Modules\Erp\Repositories\NavErpOrderMapperRepository;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;


class NavErpOrderMapperController extends BaseController
{
    protected $repository;
    public $tarnsformer;

    public function __construct(NavErpOrderMapper $navErpOrderMapper, NavErpOrderMapperRepository $navErpOrderMapperRepository)
    {
        $this->repository = $navErpOrderMapperRepository;
        $this->model = $navErpOrderMapper;
        $this->model_name = "Nav Erp Order Mapper";

        parent::__construct($this->model, $this->model_name);

        $this->transformer = new NavErpOrderMapperResource(array());
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "website_id" => "required|exists:websites,id",
            ]);

            $fetched = $this->repository->fetchAll($request, callback: function () use ($request) {
                return $this->model->whereWebsiteId($request->website_id);
            });
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
                $data = $request->validate([
                    "country_id" => [
                        Rule::requiredIf(!$request->is_default),
                        Rule::unique("nav_erp_order_mappers")
                            ->ignore($this->model->id, "country_id"),
                        "exists:countries,id",
                    ],
                ]);
                return $data;
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
            $data = $this->repository->validateData($request);
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
}
