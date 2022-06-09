<?php

namespace Modules\Country\Http\Controllers\StoreFront;

use Elasticsearch\Endpoints\Count;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Entities\Website;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Country\Entities\City;
use Modules\Country\Entities\Country;
use Modules\Country\Repositories\CityRepository;
use Modules\Country\Transformers\CityResource;
use Exception;

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

    public function index(Request $request, int $country_id, int $region_id): JsonResponse
    {
        try
        {
            $request->without_pagination = true;
            $fetched = $this->repository->fetchAll($request, callback:function () use($region_id){
                return ($region_id) ? $this->model::whereRegionId($region_id) : $this->model;

            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }
}
