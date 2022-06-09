<?php

namespace Modules\Country\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Country\Entities\Country;
use Exception;
use Modules\Country\Repositories\CountryRepository;
use Modules\Country\Transformers\CountryResource;

class CountryController extends BaseController
{
    private $repository;

    public function __construct(Country $country, CountryRepository $countryRepository)
    {
        $this->model = $country;
        $this->model_name = "Country";
        parent::__construct($this->model, $this->model_name);
        $this->repository = $countryRepository;
    }

    public function collection(object $data): ResourceCollection
    {
        return CountryResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CountryResource($data);
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
            $fetched = $this->repository->fetch($id, [ "regions", "regions.cities" ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function channelCountry(int $channel_id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->getChannelCountry($channel_id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }
}
