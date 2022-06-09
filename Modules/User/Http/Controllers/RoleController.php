<?php

namespace Modules\User\Http\Controllers;

use Modules\Core\Tree;
use Illuminate\Http\Request;
use Modules\User\Entities\Role;
use Illuminate\Http\JsonResponse;
use Modules\User\Transformers\RoleResource;
use Modules\User\Repositories\RoleRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\User\Exceptions\RoleHasAdminsException;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoleController extends BaseController
{
    protected $repository;

    public function __construct(Role $role, RoleRepository $roleRepository)
    {
        $this->middleware('admin');
        $this->repository = $roleRepository;
        $this->model = $role;
        $this->model_name = "Role";
        $exception_statuses = [
            RoleHasAdminsException::class => 403
        ];

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function collection(object $data): ResourceCollection
    {
        return RoleResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new RoleResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $this->validateListFiltering($request);
            $fetched = $this->getFilteredList($request);
        }
        catch (\Exception $exception)
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
            if ( $request->permission_type == "custom" ) $this->repository->checkPermissionExists($request->permissions);
            if ( $request->slug == null ) $data["slug"] = $this->model->createSlug($request->name);
            if ( $request->permission_type != "custom" ) $data["permissions"] = [];

            $created = $this->repository->create($data);
        }
        catch (\Exception $exception)
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
        catch (\Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function fetchPermission(): JsonResponse
    {
        try
        {
            $acl = $this->createACL();
            $this->model_name = "Permission";
        }
        catch (\Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($acl->items, $this->lang('fetch-success', ["name" => "Permissions"]));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, [
                "slug" => "nullable|unique:roles,slug,{$id}"
            ]);
            
            if ( $request->permission_type == "custom" ) $this->repository->checkPermissionExists($request->permissions);
            if ( $request->slug == null ) $data["slug"] = $this->model->createSlug($request->name);
            if ( $request->permission_type != "custom" ) $data["permissions"] = [];

            $updated = $this->repository->update($data, $id);
        }
        catch (\Exception $exception)
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
                if ( $deleted->admins_count > 0 ) throw new RoleHasAdminsException("Admins are present with this role.");
            });
        }
        catch (\Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    public function createACL(): Tree
    {
        $tree = Tree::create();
        foreach (config('acl') as $item) {
            $tree->add($item, 'acl');
        }
        return $tree;
    }
}
