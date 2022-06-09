<?php

namespace Modules\Country\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Country\Entities\City;
use Exception;
use Modules\Country\Repositories\CityRepository;
use Modules\Country\Transformers\CityResource;

class CityController extends BaseController
{
    private $repository;

    public function __construct(City $city, CityRepository $cityRepository)
    {
        $this->model = $city;
        $this->model_name = "City";
        parent::__construct($this->model, $this->model_name);
        $this->repository = $cityRepository;
    }

    public function collection(object $data): ResourceCollection
    {
        return CityResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CityResource($data);
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
            $fetched = $this->repository->fetch($id, [ "region" ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function regionWiseCity(Request $request): JsonResponse
    {
        try
        {
            $request->without_pagination = true;
            $fetched = $this->repository->fetchAll($request, callback:function () use($request) {
                return $this->model->whereRegionId($request->region_id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }
}
