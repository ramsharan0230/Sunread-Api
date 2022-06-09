<?php

namespace Modules\Country\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Exception;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Country\Entities\Region;
use Modules\Country\Repositories\RegionRepository;
use Modules\Country\Transformers\RegionResource;

class RegionController extends BaseController
{
    private $repository;

    public function __construct(Region $region, RegionRepository $regionRepository)
    {
        $this->model = $region;
        $this->model_name = "Region";
        parent::__construct($this->model, $this->model_name);
        $this->repository = $regionRepository;
    }

    public function collection(object $data): ResourceCollection
    {
        return RegionResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new RegionResource($data);
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

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, [ "cities" ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function countryWiseRegion(Request $request): JsonResponse
    {
        try
        {
            $request->without_pagination = true;
            $fetched = $this->repository->fetchAll($request, callback:function () use($request) {
                return $this->model->whereCountryId($request->country_id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }
}
