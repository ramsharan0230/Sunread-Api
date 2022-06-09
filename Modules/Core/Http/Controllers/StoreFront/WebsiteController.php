<?php

namespace Modules\Core\Http\Controllers\StoreFront;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Modules\Core\Repositories\StoreFront\WebsiteRepository;
use Modules\Core\Entities\Website;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Core\Transformers\StoreFront\WebsiteResource;

class WebsiteController extends BaseController
{
    protected $repository;
    public array $relations = [
        "channels",
        "stores",
    ];

    public function __construct(Website $website, WebsiteRepository $websiteRepository)
    {
        $this->model = $website;
        $this->model_name = "Website";
        $this->repository = $websiteRepository;
        $this->middleware('validate.website.host');

        parent::__construct($this->model, $this->model_name);
        $this->transformer = new WebsiteResource(array());
    }

    public function show(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "host" => "required|exists:websites,hostname",
            ]);

            $fetched = $this->repository->queryFetch(["hostname" => $request->host], $this->relations);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($fetched),
            message: $this->lang("fetch-success"),
        );
    }
}
