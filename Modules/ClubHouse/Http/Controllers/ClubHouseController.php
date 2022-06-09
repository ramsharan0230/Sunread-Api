<?php

namespace Modules\ClubHouse\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Core\Rules\ScopeRule;
use Modules\ClubHouse\Entities\ClubHouse;
use Modules\ClubHouse\Rules\SlugUniqueRule;
use Modules\ClubHouse\Rules\ClubHouseScopeRule;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\ClubHouse\Transformers\ClubHouseResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\ClubHouse\Repositories\ClubHouseRepository;
use Modules\ClubHouse\Repositories\ClubHouseValueRepository;

class ClubHouseController extends BaseController
{
    // Protected properties
    protected $repository, $clubHouseValueRepository;

    /**
     * ClubHouseController Class constructor
     */
    public function __construct(ClubHouseRepository $clubHouseRepository, ClubHouse $clubHouse, ClubHouseValueRepository $clubHouseValueRepository)
    {
        $this->repository = $clubHouseRepository;
        $this->clubHouseValueRepository = $clubHouseValueRepository;

        $this->model = $clubHouse;
        $this->model_name = "Club House";

        // Calling Parent Constructor of BaseController
        parent::__construct($this->model, $this->model_name);
    }

    /**
     * Returns ClubHouseResource in Collection
     */
    public function collection(object $data): ResourceCollection
    {
        return ClubHouseResource::collection($data);
    }

    /**
     * Returns ClubHouseResource
     */
    public function resource(object $data): JsonResource
    {
        return new ClubHouseResource($data);
    }

    /**
     * Fetches and returns the list of ClubHouse
     */
    public function index(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "scope" => "sometimes|in:website,channel,store",
                "scope_id" => [ "sometimes", "integer", "min:1", new ScopeRule($request->scope), new ClubHouseScopeRule($request)],
                "website_id" => "sometimes|exists:websites,id"
            ]);
            $fetched = $this->repository->fetchAll($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang("fetch-list-success"));
    }

    /**
     * Validates and Creates Clubhouse with Clubhouse values
     */
    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->clubHouseValueRepository->validateWithValuesCreate($request);

            $created = $this->repository->create($data, function ($created) use ($data) {
                $this->clubHouseValueRepository->createOrUpdate($data, $created);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang("create-success"), 201);
    }

    /**
     * Fetches and returns the ClubHouse by Id
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try
        {
            $request->validate([
                "scope" => "sometimes|in:website,channel,store",
                "scope_id" => [ "sometimes", "integer", "min:1", new ScopeRule($request->scope), new ClubHouseScopeRule($request, $id)]
            ]);
            $club_house = $this->model->findOrFail($id);
            $fetched = $this->repository->fetchWithAttributes($request, $club_house);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }

    /**
     * Validates and Updates Clubhouse with Clubhouse values
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $club_house = $this->model->findOrFail($id);

            $data = $this->clubHouseValueRepository->validateWithValuesUpdate($request, $club_house);

            $updated = $this->repository->update($data, $id, function ($updated) use ($data) {
                $this->clubHouseValueRepository->createOrUpdate($data, $updated);
                $updated->load("values");
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("update-success"));
    }

    /**
     * Finds and Deletes Clubhouse
     */
    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->model->findOrFail($id);

            $this->repository->delete($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    /**
     * Updates the Status of Clubhouse with given Id
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try
        {
            $updated = $this->repository->updateStatus($request, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("status-updated"));
    }

    /**
     * Fetches and returns Attributes for ClubHouse Values
     */
    public function attributes(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "scope" => "sometimes|in:website,channel,store"
            ]);

            $fetched = $this->repository->getConfigData([
                "scope" => $request->scope ?? "website"
            ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang("fetch-success"));
    }

}
