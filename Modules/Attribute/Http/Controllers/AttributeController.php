<?php

namespace Modules\Attribute\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Attribute\Entities\Attribute;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Attribute\Transformers\AttributeResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Attribute\Repositories\AttributeRepository;
use Modules\Attribute\Repositories\AttributeOptionRepository;
use Modules\Attribute\Exceptions\AttributeTranslationDoesNotExist;
use Modules\Attribute\Repositories\AttributeTranslationRepository;
use Modules\Attribute\Exceptions\AttributeTranslationOptionDoesNotExist;
use Modules\Attribute\Exceptions\AttributeNotUserDefinedException;
use Modules\Attribute\Exceptions\AttributeCannotChangeException;
use Illuminate\Support\Str;

class AttributeController extends BaseController
{
    protected $repository, $translation_repository, $option_repository;

    public function __construct(Attribute $attribute, AttributeRepository $attributeRepository, AttributeTranslationRepository $attributeTranslationRepository, AttributeOptionRepository $attributeOptionRepository)
    {
        $this->repository = $attributeRepository;
        $this->model = $attribute;
        $this->model_name = "Attribute";
        $exception_statuses = [
            AttributeTranslationDoesNotExist::class => 422,
            AttributeTranslationOptionDoesNotExist::class => 422,
            AttributeNotUserDefinedException::class => 403,
            AttributeCannotChangeException::class => 403
        ];

        $this->translation_repository = $attributeTranslationRepository;
        $this->option_repository = $attributeOptionRepository;

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function collection(object $data): ResourceCollection
    {
        return AttributeResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new AttributeResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, ["translations", "attribute_options.translations"]);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $type_check = in_array($request->type, $this->repository->non_filterable_fields);
            $rules = $type_check ? [ "attribute_options" => "required|array" ] : [ "default_value" => [ "nullable", config("validation.{$request->type}") ] ];
            $data = $this->repository->validateData($request, $rules,  function() use ($request) {
                return [
                    'slug' => $request->slug ? Str::slug($request->slug, "_") : $this->model->createSlug($request->name, slug:Str::slug($request->name, "_"))
                ];
            });
            $this->repository->validateTranslation($request);
            $data = $this->repository->preparingData($request, $data);

            $created = $this->repository->create($data, function($created) use ($request, $type_check) {
                $this->translation_repository->updateOrCreate($request->translations, $created);
                if ($type_check) $this->option_repository->updateOrCreate($request->attribute_options, $created);
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $modelData = $this->repository->fetch($id);
            $fetched = $modelData->toArray();
            if(in_array($fetched["type"], $this->repository->non_filterable_fields)) unset($fetched["default_value"]) ;
            $fetched["translations"] = $this->translation_repository->show($id);
            $fetched["attribute_options"] = $modelData->getConfigOption() ?? $this->option_repository->show($id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $type_check = in_array($request->type, $this->repository->non_filterable_fields);
            $rules = $type_check ? [ "attribute_options" => "required|array" ] : [ "default_value" => [ "nullable", config("validation.{$request->type}") ] ];
            
            $data = $this->repository->validateData($request, array_merge($rules, [
                "slug" => "nullable|unique:attributes,slug,{$id}"
            ]), function() use ($request) {
                return [
                    'slug' => $request->slug ?? $this->model->createSlug($request->name, slug:Str::slug($request->name, "_"))
                ];
            });

            $this->repository->validateFieldOnUpdate($data, $id);
            $this->repository->validateTranslation($request);

            $data = $this->repository->preparingData($request, $data);

            $updated = $this->repository->update($data, $id, function($updated) use ($request, $type_check) {
                $this->translation_repository->updateOrCreate($request->translations, $updated);
                if ($type_check) $this->option_repository->updateOrCreate($request->attribute_options, $updated, "update");
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
            $this->repository->delete($id, function($deleted) {
                if (!$deleted->is_user_defined) throw new AttributeNotUserDefinedException(__("core::app.response.delete-failed", ["name" => $this->model_name]));
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try
        {
            $unauthorized_delete_count = 0;
            $this->repository->bulkDelete($request, function ($query) use ($request, &$unauthorized_delete_count){
                $unauthorized_delete_count = $this->model->whereIn("id", $request->ids)->where("is_user_defined", 0)->count();  
                return $query->where("is_user_defined", 1);
            });

            $message = $unauthorized_delete_count ? "Couldn't delete {$unauthorized_delete_count} items" : $this->lang('delete-success');
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($message);
    }

    public function types()
    {
        try
        {
            $types = array_keys(config("attribute_types"));
            $fetched = array_filter(array_map(function($data) {
                if(!in_array($data, [ "categoryselect", "productimages", "builder" ])) return $data;
            }, $types));
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-list-success'));

    }
}
