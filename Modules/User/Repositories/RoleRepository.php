<?php

namespace Modules\User\Repositories;

use Modules\User\Entities\Role;
use Illuminate\Validation\ValidationException;
use Modules\Core\Repositories\BaseRepository;

class RoleRepository extends BaseRepository
{
    public function __construct(Role $role)
    {
        $this->model = $role;
        $this->model_key = "role";

        $this->rules = [
            "name" => "required",
            "slug" => "nullable|unique:roles,slug",
            "description" => "nullable",
            "permission_type" => "required|in:all,custom",
            "permissions" => "sometimes"
        ];
    }

    public function checkPermissionExists(array $permissions): void
    {
        $all_permissions = array_column(config("acl"), "key");
        if(array_diff($permissions, $all_permissions)){
            throw ValidationException::withMessages([
               "permissions" => "Invalid permissions."
            ]);
        };
    }
}
