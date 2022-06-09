<?php

namespace Modules\Attribute\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Attribute\Entities\AttributeGroup;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Attribute\Exceptions\AttributesPresent;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Attribute\Transformers\AttributeGroupResource;
use Modules\Attribute\Repositories\AttributeGroupRepository;
use Modules\Attribute\Exceptions\AttributeTranslationDoesNotExist;
use Modules\Attribute\Repositories\AttributeGroupTranslationRepository;

class AttributeGroupController extends BaseController
{
    protected $repository, $translation_repository;

    public function __construct(AttributeGroup $attribute_group, AttributeGroupRepository $attributeGroupRepository, AttributeGroupTranslationRepository $attributeGroupTranslationRepository)
    {
        $this->repository = $attributeGroupRepository;
        $this->translation_repository = $attributeGroupTranslationRepository;
        $this->model = $attribute_group;
        $this->model_name = "Attribute Group";
        $exception_statuses = [
            AttributesPresent::class => 403,
            AttributeTranslationDoesNotExist::class => 422
        ];

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function collection(object $data): ResourceCollection
    {
        return AttributeGroupResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new AttributeGroupResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $this->validateListFiltering($request);
            $fetched = $this->getFilteredList($request, ["attribute_family"]);
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
            $data = $this->repository->validateData($request);
            if ( $request->slug == null ) $data["slug"] = $this->model->createSlug($request->name);

            $created = $this->repository->create($data, function($created) use ($request) {
                $this->translation_repository->updateOrCreate($request->translations, $created);
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
            $fetched = $this->model->findOrFail($id);
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
            $data = $this->repository->validateData($request, [
                "slug" => "nullable|unique:attribute_groups,slug,{$id}"
            ]);
            if ( $request->slug == null ) $data["slug"] = $this->model->createSlug($request->name);

            $updated = $this->repository->update($data, $id, function($updated) use ($request) {
                $this->translation_repository->updateOrCreate($request->translations, $updated);
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
                if ( count($deleted->attributes) > 0 ) throw new AttributesPresent("Attribute Groups present in family.");
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }
}
