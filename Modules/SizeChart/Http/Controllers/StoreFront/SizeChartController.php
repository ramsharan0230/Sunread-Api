<?php

namespace Modules\SizeChart\Http\Controllers\StoreFront;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Repositories\StoreFront\SizeChartRepository;
use Exception;
use Modules\SizeChart\Transformers\SizeChartResource;
use Modules\Core\Facades\CoreCache;

class SizeChartController extends BaseController
{
    protected $repository;
    protected $sizeChartScopeRepository;

    public function __construct(
        SizeChart $sizeChart,
        SizeChartRepository $sizeChartRepository,
    )
    {
        $this->model = $sizeChart;
        $this->model_name = "Size Chart";
        $this->repository = $sizeChartRepository;

        $this->middleware('validate.website.host')->only(['show', 'index']);
        $this->middleware('validate.channel.code')->only(['show']);
        $this->middleware('validate.store.code')->only(['show']);

        parent::__construct($this->model, $this->model_name);

        $this->transformer = new SizeChartResource(array());
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $website = CoreCache::getWebsite($request->header("hc-host"));
            $fetched = $this->repository->fetchAll($request, [ "size_chart_scopes", "website", "size_chart_contents" ], callback:function () use ($website) {
                return $this->model->whereWebsiteId($website->id)->where("status", 1);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->collection($fetched),
            message: $this->lang("fetch-list-success")
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        try
        {
            $fetched = $this->repository->sizeChart($request, $slug);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $fetched,
            message: $this->lang("fetch-success")
        );
    }
}
