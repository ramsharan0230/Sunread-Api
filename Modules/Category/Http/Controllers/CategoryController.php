<?php

namespace Modules\Category\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Category\Entities\Category;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Category\Transformers\CategoryResource;
use Modules\Category\Repositories\CategoryRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Category\Transformers\List\CategoryResource as ListCategoryResource;
use Modules\Category\Repositories\CategoryValueRepository;
use Modules\Category\Rules\CategoryScopeRule;
use Modules\Category\Rules\SlugUniqueRule;
use Modules\Core\Rules\ScopeRule;
use Modules\Product\Entities\Product;
use Illuminate\Support\Str;

class CategoryController extends BaseController
{
    protected $repository, $categoryValueRepository, $is_super_admin, $main_root_id, $product_model;

    public function __construct(CategoryRepository $categoryRepository, Category $category, CategoryValueRepository $categoryValueRepository, Product $product_model)
    {
        $this->repository = $categoryRepository;
        $this->categoryValueRepository = $categoryValueRepository;

        $this->model = $category;
        $this->model_name = "Category";

        $this->product_model = $product_model;

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return CategoryResource::collection($data);
    }

    public function listCollection(object $data): ResourceCollection
    {
        return ListCategoryResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CategoryResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "scope" => "sometimes|in:website,channel,store",
                "scope_id" => [ "sometimes", "integer", "min:1", new ScopeRule($request->scope), new CategoryScopeRule($request)],
                "website_id" => "required|exists:websites,id"
            ]);
            $fetched = $this->repository->fetchAll(request: $request, callback: function () use ($request) {
                return $this->model->whereWebsiteId($request->website_id)->orderBy("_lft", "asc");
            })->toTree();
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->listCollection($fetched), $this->lang("fetch-list-success"));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, array_merge($this->categoryValueRepository->getValidationRules($request), [
                "items.slug.value" => new SlugUniqueRule($request),
                "website_id" => "required|exists:websites,id"
            ]), function () use ($request) {
                return [
                    "scope" => "website",
                    "scope_id" => $request->website_id
                ];
            });

            if(isset($data["items"]["slug"]["value"])) $data["items"]["slug"]["value"] = Str::slug($data["items"]["slug"]["value"]);
            else $data["items"]["slug"]["value"] = $this->repository->createUniqueSlug($data);

            if(isset($data["parent_id"])) if(strcmp(strval($this->model->find($data["parent_id"])->website_id), $data["website_id"]))
            throw ValidationException::withMessages(["website_id" => $this->lang("response.no_parent_belong_to_website")]);

            $created = $this->repository->create($data, function ($created) use ($data) {
                $this->categoryValueRepository->createOrUpdateValue($data, $created);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang("create-success"), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try
        {
            $request->validate([
                "scope" => "sometimes|in:website,channel,store",
                "scope_id" => [ "sometimes", "integer", "min:1", new ScopeRule($request->scope), new CategoryScopeRule($request, $id)]
            ]);

            $category = $this->model->findOrFail($id);
            $data = [
                "scope" => $request->scope ?? "website",
                "scope_id" => $request->scope_id ?? $category->website_id,
                "category_id" => $id
            ];

            $name_data = array_merge($data, ["attribute" => "name"]);
            $category->createModel();
            $nameValue = $category->has($name_data) ? $category->getValues($name_data) : $category->getDefaultValues($name_data);

            $fetched = [];
            $fetched = [
                "id" => $id,
                "parent_id" => $category->parent_id,
                "website_id" => $category->website_id,
                "name" => $nameValue?->value
            ];
            $fetched["attributes"] = $this->repository->getConfigData($data);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $category = $this->model->findOrFail($id);
            $data = $this->repository->validateData($request, array_merge($this->categoryValueRepository->getValidationRules($request, $id, "update"), [
                "items.slug.value" => new SlugUniqueRule($request, $category),
                "scope" => "required|in:website,channel,store",
                "scope_id" => [ "required", "integer", "min:1", new ScopeRule($request->scope), new CategoryScopeRule($request, $id)]
            ]), function () use ($category) {
                return [
                    "parent_id" => $category->parent_id,
                    "website_id" => $category->website_id
                ];
            });

            if(isset($data["items"]["slug"]["value"])) $data["items"]["slug"]["value"] = Str::slug($data["items"]["slug"]["value"]);
            else {
                if(!isset($data["items"]["slug"]["use_default_value"])) $data["items"]["slug"]["value"] = $this->repository->createUniqueSlug($data, $category);
            }

            $updated = $this->repository->update($data, $id, function ($updated) use ($data) {
                $this->categoryValueRepository->createOrUpdateValue($data, $updated);
                $updated->load("values");
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("update-success"));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $category = $this->model->findOrFail($id);

            $this->repository->delete($id);
        }
        catch (Exception $exception)
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

    public function attributes(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "scope" => "sometimes|in:website,channel,store"
            ]);

            $fetched = $this->repository->getConfigData([
                "scope" => $request->scope ?? "website"
            ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang("fetch-success"));
    }

    public function updatePosition(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $request->validate([
                "parent_id" => "nullable|numeric|exists:categories,id",
                "position" => "required|numeric",
                "scope" => "required|in:website,channel,store",
                "scope_id" => [ "required", "integer", "min:1", new ScopeRule($request->scope), new CategoryScopeRule($request, $id)]
            ]);

            $category = $this->repository->updatePosition($data, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($category), $this->lang("update-success"));
    }
}
