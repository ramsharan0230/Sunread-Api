<?php

namespace Modules\Product\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Product\Entities\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\Transformers\ProductResource;
use Modules\Product\Repositories\ProductRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Rules\ScopeRule;
use Modules\Product\Entities\ProductAttribute;
use Modules\Product\Exceptions\ProductAttributeCannotChangeException;
use Modules\Product\Repositories\ProductAttributeRepository;
use Modules\Product\Rules\WebsiteWiseScopeRule;
use Modules\Product\Transformers\List\ProductResource as ListProductResource;
use Modules\Product\Transformers\VariantProductResource;

class ProductController extends BaseController
{
    protected $repository, $product_attribute_repository;

    public function __construct(Product $product, ProductRepository $productRepository, ProductAttributeRepository $productAttributeRepository)
    {
        $this->model = $product;
        $this->model_name = "Product";
        $this->repository = $productRepository;
        $this->product_attribute_repository = $productAttributeRepository;

        $exception_statuses = [
            ProductAttributeCannotChangeException::class => 403
        ];

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function collection(object $data): ResourceCollection
    {
        return ProductResource::collection($data);
    }

    public function listCollection(object $data): ResourceCollection
    {
        return ListProductResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ProductResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, ["categories"], function () use ($request) {
                return $this->repository->getFilterProducts($request);
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->listCollection($fetched), $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, [
                "website_id" => "required|exists:websites,id",
                "attribute_set_id" => "required|exists:attribute_sets,id"
            ], function ($request) {
                return [
                    "scope" => "website",
                    "scope_id" => $request->website_id,
                    "type" => "simple"
                ];
            });

            $scope = [
                "scope" => $data["scope"],
                "scope_id" => $data["scope_id"]
            ];

            $created = $this->repository->create($data, function(&$created) use($request, $scope) {
                $attributes = $this->product_attribute_repository->validateAttributes($created, $request, $scope, "store");
                $this->product_attribute_repository->syncAttributes($attributes, $created, $scope, $request);
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, ["categories", "parent", "brand", "website", "product_attributes", "productBuilderValues", "catalog_inventories", "variants"]);
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
            $product = $this->model::findOrFail($id);
            $data = $this->repository->validateData($request, [
                "scope_id" => ["sometimes", "integer", "min:0", new ScopeRule($request->scope), new WebsiteWiseScopeRule($request->scope ?? "website", $product->website_id)]
            ], function ($request) use($product) {
                return [
                    "scope" => $request->scope ?? "website",
                    "scope_id" => $request->scope_id ?? $product->website_id,
                    "type" => "simple",
                    "website_id" => $product->website_id
                ];
            });

            $scope = [
                "scope" => $data["scope"],
                "scope_id" => $data["scope_id"]
            ];

            $updated = $this->repository->update($data, $id, function($updated) use($request, $scope) {
                $attributes = $this->product_attribute_repository->validateAttributes($updated, $request, $scope, "update");
                $this->product_attribute_repository->syncAttributes($attributes, $updated, $scope, $request, "update");
            });
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
            $this->repository->delete($id, function ($deleted) {
                ProductAttribute::preventLazyLoading(false);
                $deleted->product_attributes()->each(function ($product_attribute) {
                    $product_attribute->delete();
                });
                
                $deleted->variants()->each(function ($variant) {
                    $this->repository->delete($variant->id);
                });
            });
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

    public function categoryWiseProducts(Request $request, $category_id): JsonResponse
    {
        try
        {
            $this->validateListFiltering($request);
            $allData = $this->getFilteredList($request);
            foreach($allData as &$data)
            {
                $data->selected = in_array($category_id, $data->categories()->pluck('id')->toArray()) ? 1 : 0;
                $fetched[] = $data;
            }
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-list-success'));
    }

    public function product_attributes(Request $request, int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->product_attribute_data($id, $request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }

    public function variants(Request $request, int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, callback:function () use ($request, $id) {
                return $this->repository->getVariants($request, $id);
            });
        }
        catch ( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(VariantProductResource::collection($fetched), $this->lang('fetch-success'));
    }

    public function configurations(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->configurations($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success', ["name" => "Configuration"]));
    }

    
}
