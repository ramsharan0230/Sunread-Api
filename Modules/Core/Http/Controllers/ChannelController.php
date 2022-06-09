<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Core\Entities\Channel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Repositories\ChannelRepository;
use Modules\Core\Transformers\ChannelResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Modules\Core\Rules\FQDN;

class ChannelController extends BaseController
{
    protected $repository;

    public function __construct(Channel $channel, ChannelRepository $channelRepository)
    {
        $this->model = $channel;
        $this->model_name = "Channel";
        $this->repository = $channelRepository;

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return ChannelResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ChannelResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, [ "website", "stores" ],  function () use ($request) {
                $request->validate([
                    "website_id" => "sometimes|exists:websites,id"
                ]);
                return $request->website_id ? $this->model->whereWebsiteId($request->website_id) : $this->model;
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request);

            unset($data["default_store_id"]);

            $created = $this->repository->create($data);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, ["default_store", "stores", "website"]);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, [
                "code" => "required|unique:channels,code,{$id}",
                "hostname" => [ "nullable", "unique:websites,hostname", "unique:channels,hostname,{$id}", new FQDN()]
            ], function () use ($id) {
                $channel = $this->model->findOrFail($id);
                return [
                    "website_id" => $channel->website_id
                ];
            });

            if(isset($data['default_store_id'])) $this->repository->defaultStoreValidation($data, $id);

            $updated = $this->repository->update($data, $id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('update-success'));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id);
        }
        catch( Exception $exception )
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
