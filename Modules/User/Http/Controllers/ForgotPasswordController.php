<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Modules\User\Entities\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\PasswordBroker;
use Modules\Core\Http\Controllers\BaseController;
use Modules\User\Exceptions\AdminNotFoundException;
use Modules\Customer\Exceptions\TokenGenerationException;

class ForgotPasswordController extends BaseController
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

    public function store(Request $request): JsonResponse
    {
        try
        {
            $this->validate($request, ["email" => "required|email"]);

            $admin = $this->model::where("email", $request->email)->firstOrFail();
            $response = $this->broker()->sendResetLink(["email" => $admin->email]);

            if ($response == Password::INVALID_TOKEN) throw new TokenGenerationException(__("core::app.users.token.token-generation-problem"));
            if ($response == Password::INVALID_USER) throw new AdminNotFoundException("Admin not found.");
        }
        catch (\Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage("Reset Link sent to your email {$admin->email}");
    }

    public function broker(): PasswordBroker
    {
        return Password::broker('admins');
    }
}
