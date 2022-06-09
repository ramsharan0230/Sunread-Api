<?php

namespace Modules\Country\Http\Controllers\StoreFront;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Facades\SiteConfig;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Country\Entities\Country;
use Modules\Country\Repositories\CountryRepository;
use Modules\Country\Transformers\CountryResource;
use Exception;
use Modules\Customer\Repositories\StoreFront\AddressRepository;

class CountryController extends BaseController
{
    private $repository, $addressRepository;

    public function __construct(Country $country, CountryRepository $countryRepository, AddressRepository $addressRepository)
    {
        $this->model = $country;
        $this->model_name = "Country";
        parent::__construct($this->model, $this->model_name);
        $this->repository = $countryRepository;
        $this->addressRepository = $addressRepository;
    }

    public function collection(object $data): ResourceCollection
    {
        return CountryResource::collection($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $request->without_pagination = true;
            $fetched = $this->repository->fetchAll($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function channelCountries(Request $request): JsonResponse
    {
        try
        {
            $core_cache = $this->repository->getCoreCache($request);
            $fetched = $this->addressRepository->getCountry($core_cache);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }
}
