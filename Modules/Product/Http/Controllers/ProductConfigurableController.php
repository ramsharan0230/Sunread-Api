<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Repositories\ProductConfigurableRepository;
use Modules\Product\Entities\Product;
use Modules\Product\Transformers\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\Exceptions\ProductAttributeCannotChangeException;
use Exception;
use Modules\Core\Rules\ScopeRule;
use Modules\Product\Repositories\ProductAttributeRepository;
use Modules\Product\Repositories\ProductRepository;
use Modules\Product\Rules\WebsiteWiseScopeRule;

class ProductConfigurableController extends BaseController
{
    protected $repository, $product_repository, $product_attribute_repository;

    public function __construct(Product $product, ProductConfigurableRepository $productConfigurableRepository, ProductRepository $product_repository, ProductAttributeRepository $productAttributeRepository)
    {
        $this->model = $product;
        $this->model_name = "Product";
        $this->repository = $productConfigurableRepository;
        $this->product_repository = $product_repository;
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

    public function resource(object $data): JsonResource
    {
        return new ProductResource($data);
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
                    "type" => "configurable"
                ];
            });
            
            $scope = [
                "scope" => $data["scope"],
                "scope_id" => $data["scope_id"]
            ];

            $created = $this->repository->create($data, function (&$created) use ($request, $scope) {
                $attributes = $this->product_attribute_repository->validateAttributes($created, $request, $scope, "store", "configurable");
                $this->product_attribute_repository->syncAttributes($attributes, $created, $scope, $request, "store", "configurable");

                $this->repository->createVariants($created, $request, $scope, $attributes);
            });
        }
        catch(Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang("create-success"), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $product = $this->model::findOrFail($id);
            $data = $this->repository->validateData($request, [
                "scope_id" => ["sometimes", "integer", "min:0", new ScopeRule($request->scope), new WebsiteWiseScopeRule($request->scope ?? "website", $product->website_id)],
                "update_configurable_attributes" => "required|boolean"
            ], function ($request) use($product) {
                return [
                    "scope" => $request->scope ?? "website",
                    "scope_id" => $request->scope_id ?? $product->website_id,
                    "type" => "configurable",
                    "website_id" => $product->website_id
                ];
            });

            $scope = [
                "scope" => $data["scope"],
                "scope_id" => $data["scope_id"]
            ];

            $updated = $this->repository->update($data, $id, function(&$updated) use($request, $scope, $data) {
                $attributes = $this->product_attribute_repository->validateAttributes($updated, $request, $scope, "update", "configurable");
                $this->product_attribute_repository->syncAttributes($attributes, $updated, $scope, $request, "update", "configurable");

                $this->repository->updateVariants($data, $request, $updated, $scope, $attributes);

                $updated->load("variants");
            });
        }
        catch(Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("update-success"));
    }

}
