<?php

namespace Modules\User\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Modules\User\Entities\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Modules\User\Exceptions\AdminAlreadyActiveException;
use Modules\User\Transformers\AdminResource;
use Modules\User\Repositories\AdminRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\User\Exceptions\CannotDeleteSelfException;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\User\Exceptions\CannotDeleteSuperAdminException;

class UserController extends BaseController
{
    protected $repository;

    public function __construct(Admin $admin, AdminRepository $adminRepository)
    {
        $this->middleware('admin');
        $this->repository = $adminRepository;
        $this->model = $admin;
        $this->model_name = "Admin";
        $exception_statuses = [
            CannotDeleteSelfException::class => 403,
            CannotDeleteSuperAdminException::class => 403,
            AdminAlreadyActiveException::class => 403
        ];

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function collection(object $data): ResourceCollection
    {
        return AdminResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new AdminResource($data);
    }

    public function index(Request  $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, ["role"]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, [
                "status" => "sometimes|boolean",
                "is_invite" => "sometimes|boolean",
                "password" => "required_unless:is_invite,true|confirmed",
                "role_id" => "required|integer|exists:roles,id"
            ]);

            $created = $this->repository->create($data, function ($created) use ($request) {
                $created->load("role");

                if ( $request->is_invite == true ) {
                    $created = $this->repository->storeInvitation($created);
                } else {
                    $created->password = Hash::make($request->password);
                    $created->save();
                }
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, ["role"]);
        }
        catch (Exception $exception)
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
                "email" => "required|email|unique:admins,email,{$id}",
                "password" => is_null($request->password) ? "sometimes|nullable" : "required|confirmed",
                "status" => "sometimes|boolean",
                "role_id" => "required|integer|exists:roles,id"
            ]);
            if ( is_null($request->password) ) {
                unset($data["password"]);
            } else {
                $data["password"] = Hash::make($data["password"]);
            }

            $updated = $this->repository->update($data, $id, function ($updated) {
                $updated->load("role");
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('update-success'));
    }

    public function destroy($id): JsonResponse
    {
        try
        {
            $this->repository->delete($id, function($deleted) {
                if ( $deleted->id == auth()->user()->id ) throw new CannotDeleteSelfException("Admin cannot delete itself.");
                if ( $deleted->hasRole("super-admin") ) throw new CannotDeleteSuperAdminException("Super admin cannot be deleted.");
                $this->repository->removeOldImage($deleted->id);
            });
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
            $updated = $this->repository->updateStatus($request, $id, function ($updated) {
                $updated->load("role");
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("status-updated"));
    }

    public function resendInvitation(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, ["role"]);
            if( !$fetched->invitation_token ) throw new AdminAlreadyActiveException();

            $fetched = $this->repository->storeInvitation($fetched);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }
}
