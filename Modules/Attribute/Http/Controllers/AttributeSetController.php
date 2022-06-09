<?php

namespace Modules\Attribute\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeGroup;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Attribute\Exceptions\AttributeGroupsPresent;
use Modules\Attribute\Exceptions\DefaultFamilyCanNotBeDeleted;
use Modules\Attribute\Exceptions\DefaultSetCanNotBeDeleted;
use Modules\Attribute\Repositories\AttributeGroupRepository;
use Modules\Attribute\Repositories\AttributeSetRepository;
use Modules\Attribute\Transformers\AttributeResource;
use Modules\Attribute\Transformers\AttributeSetResource;
use Modules\Attribute\Transformers\List\AttributeSetResource as ListAttributeSetResource;

class AttributeSetController extends BaseController
{
    protected $repository, $attributeGroupRepository, $group_model;

    public function __construct(AttributeSetRepository $attributeSetRepository, AttributeSet $attribute_set, AttributeGroupRepository $attributeGroupRepository, AttributeGroup $attribute_group)
    {
        $this->repository = $attributeSetRepository;
        $this->model = $attribute_set;
        $this->group_model = $attribute_group;
        $this->model_name = "Attribute Set";
        $exception_statuses = [
            DefaultFamilyCanNotBeDeleted::class => 403,
            AttributeGroupsPresent::class => 403
        ];

        $this->attributeGroupRepository = $attributeGroupRepository;

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function collection(object $data): ResourceCollection
    {
        return AttributeSetResource::collection($data);
    }

    public function listCollection(object $data): ResourceCollection
    {
        return ListAttributeSetResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new AttributeSetResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, [ "attribute_groups.attributes" ]);
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
                "attribute_set_id" => "required|exists:attribute_sets,id"
            ]);
            
            $selected_attributeSet = $this->model->find($data["attribute_set_id"]);
            
            $created = $this->repository->create($data, function($created) use ($selected_attributeSet) {
                if($selected_attributeSet->attribute_groups) 
                $selected_attributeSet->attribute_groups->map(function($attributeGroup) use($created){
                    $item = [
                        "name" => $attributeGroup->name,
                        "position" => $attributeGroup->position,
                        "attributes" => ($attributeGroup->attributes) ? $attributeGroup->attributes->pluck('id')->toArray() : []
                    ];
                    $this->attributeGroupRepository->singleUpdateOrCreate($item, $created);
                })->toArray();
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
            $fetched = $this->model->whereId($id)->with(["attribute_groups" => function($q) {
                return $q->with(['attributes' => function($query) {
                    return $query->orderBy('pivot_position', 'asc');
                }])->orderBy('position', 'asc');
            }])->firstOrFail();
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
                "groups" => "sometimes|array"
            ]);
            
            if(isset($data["groups"])) $this->repository->attributeValidation($data);

            $updated = $this->repository->update($data, $id, function($updated) use ($request) {
                if(isset($request->groups)) $this->attributeGroupRepository->multipleUpdateOrCreate($request->groups, $updated);
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
                if (!$deleted->is_user_defined) throw new DefaultSetCanNotBeDeleted($this->lang('response.default-set-delete'));
                //if ( count($deleted->attribute_groups) > 0 ) throw new AttributeGroupsPresent($this->lang('response.attribute-groups-present'));
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    public function listAttributeSets(): JsonResponse
    {
        try
        {
            $fetched = $this->model->get()->toArray();
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang("fetch-list-success"));
    }

    public function attributes(int $id): JsonResponse
    {
        try
        { 
            $fetched = $this->repository->generateFormat($id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang("fetch-success"));
    }
}
