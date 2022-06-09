<?php

namespace Modules\Category\Http\Controllers\StoreFront;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Category\Entities\Category;
use Modules\Core\Http\Controllers\BaseController;
use Exception;
use Modules\Category\Repositories\StoreFront\CategoryRepository;
use Modules\Category\Transformers\StoreFront\CategoryResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Category\Transformers\StoreFront\WebsiteWiseCategoryResource;
use Modules\Core\Repositories\StoreFront\WebsiteRepository;

class CategoryController extends BaseController
{
    protected $repository;
    public $websiteRepository;

    public function __construct(CategoryRepository $categoryRepository, Category $category, WebsiteRepository $websiteRepository)
    {
        $this->repository = $categoryRepository;
        $this->websiteRepository = $websiteRepository;
        $this->model = $category;
        $this->model_name = "Category";

        $this->middleware('validate.website.host')->only(['index', 'show']);
        $this->middleware('validate.channel.code')->only(['index']);
        $this->middleware('validate.store.code')->only(['index']);

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return CategoryResource::collection($data);
    }

    public function websiteWiseCategoryCollection(object $data): ResourceCollection
    {
        return WebsiteWiseCategoryResource::collection($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->getMenu($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang("fetch-list-success"));
    }

    public function show(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "host" => "required|exists:websites,hostname",
            ]);

            $fetched = $this->websiteRepository->queryFetch(["hostname" => $request->host], ["categories"])->categories;
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->websiteWiseCategoryCollection($fetched),
            message: $this->lang("fetch-success"),
        );
    }
}
