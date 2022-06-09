<?php

namespace Modules\ClubHouse\Http\Controllers\StoreFront;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Core\Facades\CoreCache;
use Modules\ClubHouse\Entities\ClubHouse;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\ClubHouse\Repositories\ClubHouseRepository;
use Modules\ClubHouse\Transformers\StoreFront\ClubHouseResource;

class ClubHouseController extends BaseController
{
    protected $repository;

    public function __construct(ClubHouseRepository $club_house_repository, ClubHouse $clubHouse)
    {
        $this->repository = $club_house_repository;
        $this->model = $clubHouse;
        $this->model_name = "Clubhouse";

        $this->middleware('validate.website.host');
        $this->middleware('validate.channel.code');
        $this->middleware('validate.store.code');

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return ClubHouseResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ClubHouseResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $coreCache = $this->repository->getCoreCache($request);
            $website = $coreCache->website;
            $fetched = $this->repository->fetchAll($request, callback:function () use($website) {
                return $this->model->whereHas('values', function($query) {
                    $query->where("attribute", "status")->where("value", 1);
                })->where('website_id', $website->id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang("fetch-list-success"));
    }

    public function show(Request $request, string $club_house_slug): JsonResponse
    {
        try
        {
            $coreCache = $this->repository->getCoreCache($request);
            $website = $coreCache->website;
            $fetched = $this->repository->fetchWithSlug($club_house_slug, $website);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

}
