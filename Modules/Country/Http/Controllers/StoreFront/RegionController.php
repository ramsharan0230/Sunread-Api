<?php

namespace Modules\Country\Http\Controllers\StoreFront;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Country\Entities\Region;
use Modules\Country\Repositories\RegionRepository;
use Exception;
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

    public function index(Request $request, int $country_id): JsonResponse
    {
        try
        {
            $request->without_pagination = true;
            $fetched = $this->repository->fetchAll($request, callback:function () use($country_id){
                return $this->model::whereCountryId($country_id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }
}
