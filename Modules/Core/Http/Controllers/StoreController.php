<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Modules\Core\Entities\Store;
use Illuminate\Http\JsonResponse;
use Modules\Core\Entities\Channel;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Transformers\StoreResource;
use Modules\Core\Repositories\StoreRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\Rule;

class StoreController extends BaseController
{
    protected $repository;

    public function __construct(Store $store, StoreRepository $storeRepository)
    {
        $this->model = $store;
        $this->model_name = "Store";
        $this->repository = $storeRepository;
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return StoreResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new StoreResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, ["channel.website"], callback: function () use ($request) {
                $request->validate([
                    "website_id" => "sometimes|exists:websites,id",
                    "channel_id" => "sometimes|exists:channels,id"
                ]);

                $fetched = $this->model->orderBy('position');
                if ( $request->website_id ) {
                    $channel_ids = Channel::whereWebsiteId($request->website_id)->get()->pluck("id");
                    $fetched = $fetched->whereIn("channel_id", $channel_ids);
                }
                if ( $request->channel_id ) {
                    $fetched = $fetched->whereChannelId($request->channel_id);
                }
                return $fetched;
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang("fetch-list-success"));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, [
                "code" => Rule::unique('stores')->where(function ($query) use ($request) {
                    return $query
                        ->where("channel_id", $request->channel_id)
                        ->where("code", $request->code);
                }),
            ]);
            $created = $this->repository->create($data);
        }
        catch(Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, ["channel.website"]);
        }
        catch(Exception $exception)
        {
            return $this->handleException($exception);
        }
        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request,[
                "code" => Rule::unique('stores')->where(function ($query) use ($request) {
                    return $query
                        ->where("channel_id", $request->channel_id)
                        ->where("code", $request->code);
                })->ignore($id),
            ], function () use ($id) {
                $store = $this->model->findOrFail($id);
                return [
                    "channel_id" => $store->channel_id
                ];
            });

            $updated = $this->repository->update($data, $id);
        }
        catch(Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("update-success"));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id, function ($deleted){
                if($deleted->image) Storage::delete($deleted->image);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

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
}
