<?php

namespace Modules\Product\Http\Controllers\StoreFront;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Modules\Category\Exceptions\CategoryNotFoundException;
use Modules\Category\Repositories\StoreFront\CategoryRepository;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\Entities\Product;
use Modules\Product\Exceptions\ProductNotFoundIndividuallyException;
use Modules\Product\Repositories\ProductRepository;
use Modules\Product\Repositories\ProductSearchRepository;
use Modules\Product\Repositories\StoreFront\ProductRepository as StoreFrontProductRepository;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends BaseController
{
    protected $repository;
    protected $search_repository; 
    protected $category_repository;
    protected $store_front_repository;

    public function __construct(
        Product $product, 
        ProductRepository $repository, 
        ProductSearchRepository $search_repository, 
        CategoryRepository $category_repository, 
        StoreFrontProductRepository $store_front_repository
    ) {
        $this->model = $product;
        $this->model_name = "Product";
        $this->repository = $repository;
        $this->search_repository = $search_repository;
        $this->category_repository = $category_repository;
        $this->store_front_repository = $store_front_repository;

        $this->middleware('validate.website.host');
        $this->middleware('validate.store.code');
        $this->middleware('validate.channel.code');

        $exception_statuses = [
            ProductNotFoundIndividuallyException::class => Response::HTTP_NOT_FOUND,
            CategoryNotFoundException::class => Response::HTTP_NOT_FOUND,
        ];

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function index(Request $request, string $category_slug): JsonResponse
    {
        try
        {
            $request->validate([
                "page" => "numeric|min:1",
                "color" => "sometimes|array",
                "color.*" => "sometimes|exists:attribute_options,id",
                "size" => "sometimes|array",
                "size.*" => "sometimes|exists:attribute_options,id",
                "collection" => "sometimes|array",
                "collection.*" => "sometimes|exists:attribute_options,id",
            ]);

            $all_categories = array_slice($request->segments(), 4);

            $fetched = $this->store_front_repository->categoryWiseProduct($request, $all_categories);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $fetched,
            message: $this->lang('fetch-list-success')
        );
    }

    public function category(Request $request, string $category_slug): JsonResponse
    {
        try
        {
            $all_categories = array_slice($request->segments(), 4);
            $fetched = $this->category_repository->getCategoryData($request, $all_categories);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $fetched,
            message: $this->lang('fetch-list-success')
        );
    }

    public function filter(Request $request, string $category_slug): JsonResponse
    {
        try
        {
            $all_categories = array_slice($request->segments(), 4);
            $fetched = $this->store_front_repository->getOptions($request, $all_categories);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $fetched,
            message: $this->lang('fetch-list-success')
        );
    }

    public function show(Request $request, mixed $identifier): JsonResponse
    {
        try
        {
            $data = $this->store_front_repository->show($request, $identifier);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $data,
            message: $this->lang('fetch-success')
        );
    }

    public function variantShow(Request $request, int $parent_identifier, int $identifier): JsonResponse
    {
        try
        {
            $data = $this->store_front_repository->show($request, $identifier, $parent_identifier);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $data,
            message: $this->lang('fetch-success')
        );
    }

    public function search(Request $request): JsonResponse
    {
        try
        {
            $data = $this->store_front_repository->searchWiseProduct($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $data, 
            message: $this->lang('fetch-success')
        );
    }

    public function testSearch(Request $request): JsonResponse
    {
        try
        {
            $data = $this->store_front_repository->searchTestWiseProduct($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $data, 
            message: $this->lang('fetch-success')
        );
    }
}
