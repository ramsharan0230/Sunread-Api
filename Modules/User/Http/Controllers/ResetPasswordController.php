<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\User\Entities\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\PasswordBroker;
use Modules\Core\Http\Controllers\BaseController;
use Modules\User\Exceptions\AdminNotFoundException;
use Modules\User\Exceptions\TokenGenerationException;

class ResetPasswordController extends BaseController
{
    public function __construct(Admin $admin)
    {
        $this->model = $admin;
        $this->model_name = "Admin";
        $exception_statuses = [
            TokenGenerationException::class => 401,
            AdminNotFoundException::class => 404
        ];

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function create(?string $token = null): JsonResponse
    {
        return $token
            ? $this->successResponse(['token' => $token])
            : $this->errorResponse(__('core::app.users.token-missing'), 400);
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed|min:6',
            ]);

            $response = $this->broker()->reset($data, function ($admin, $password) {
                $this->resetPassword($admin, $password);
            });

            if ($response == Password::INVALID_TOKEN) throw new TokenGenerationException(__("core::app.users.token.token-generation-problem"));
            if ($response == Password::INVALID_USER) throw new AdminNotFoundException("Admin not found.");
        }
        catch (\Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage(__("core::app.users.users.password-reset-success"));
    }

    protected function broker(): PasswordBroker
    {
        return Password::broker('admins');
    }

    protected function resetPassword(object $admin, string $password): void
    {
        $admin->password = Hash::make($password);
        $admin->setRememberToken(Str::random(60));
        $admin->save();
    }
}
