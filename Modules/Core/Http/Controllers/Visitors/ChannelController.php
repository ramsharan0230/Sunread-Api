<?php

namespace Modules\Core\Http\Controllers\Visitors;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Entities\Channel;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Core\Repositories\Visitors\ChannelRepository;
use Modules\Core\Transformers\StoreResource;

class ChannelController extends BaseController
{
    protected $repository;

    public function __construct(Channel $channel, ChannelRepository $channelRepositiory)
    {
        $this->model = $channel;
        $this->repository = $channelRepositiory;
        $this->model_name = "Channel";

        parent::__construct($this->model, $this->model_name);
    }

    public function storeCollection(object $data)
    {
        return StoreResource::collection($data);
    }

    public function stores(int $id)
    {
        try
        {
            $fetched = $this->model->with(["stores"])->findOrFail($id);
        }
        catch ( Exception $exception ) 
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->storeCollection($fetched->stores), $this->lang("fetch-list-success"));
    }
}
