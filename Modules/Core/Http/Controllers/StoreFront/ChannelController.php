<?php

namespace Modules\Core\Http\Controllers\StoreFront;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Repositories\StoreFront\ChannelRepository;
use Modules\Core\Entities\Channel;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Core\Transformers\StoreFront\ChannelResource;

class ChannelController extends BaseController
{
    protected $repository;

    public function __construct(Channel $channel, ChannelRepository $channelRepository)
    {
        $this->model = $channel;
        $this->model_name = "Channel";
        $this->repository = $channelRepository;

        $this->middleware('validate.website.host')->only(['index']);

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return ChannelResource::collection($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->getChannelList($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }
}
