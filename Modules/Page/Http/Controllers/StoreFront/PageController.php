<?php

namespace Modules\Page\Http\Controllers\StoreFront;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Exception;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Entities\Website;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Page\Entities\Page;
use Modules\Page\Repositories\StoreFront\PageRepository;
use Modules\Page\Transformers\StoreFront\PageResource;

class PageController extends BaseController
{
    protected $repository;

    public function __construct(PageRepository $repository, Page $page)
    {
        $this->repository = $repository;
        $this->model = $page;
        $this->model_name = "Page";

        $this->middleware('validate.website.host')->only(['show', 'index']);
        $this->middleware('validate.channel.code')->only(['show']);
        $this->middleware('validate.store.code')->only(['show']);

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return PageResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new PageResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $website = CoreCache::getWebsite($request->header("hc-host"));
            $fetched = $this->repository->fetchAll($request, callback:function () use ($website) {
                return $this->model->whereWebsiteId($website->id)->where("status", 1);
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        try
        {
            $fetched = $this->repository->findPage( $request, $slug );
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }
}
