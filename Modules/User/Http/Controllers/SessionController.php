<?php

namespace Modules\User\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Modules\User\Entities\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Modules\Core\Http\Controllers\BaseController;
use Modules\User\Transformers\AdminResource;

class SessionController extends BaseController
{
    public function __construct(Admin $admin)
    {
        $this->middleware('guest:admin')->except(['logout']);

        $this->model = $admin;
        $this->model_name = "Admin account";

        parent::__construct($this->model, $this->model_name);
    }

    public function login(Request $request): JsonResponse
    {
        Event::dispatch('admin.session.login.before');

        try
        {
            $data = $request->validate([
                "email" => "required|email",
                "password" => "required"
            ]);

            $jwtToken = Auth::guard("admin")
                ->setTTL(config("jwt.admin_jwt_ttl")) // Customer's JWT token time to live
                ->attempt($data);

            if (!$jwtToken) return $this->errorResponse($this->lang("login-error"), 401);

            $payload = [
                "token" => $jwtToken,
                "user" => new AdminResource(auth()->guard("admin")->user())
            ];
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        Event::dispatch('admin.session.login.after', auth()->guard("admin")->user());
        return $this->successResponse($payload, $this->lang("login-success"));
    }

    public function logout(): JsonResponse
    {
        Event::dispatch('admin.session.logout.before');

        try
        {
            $admin = auth()->guard('admin')->user();
            auth()->guard("admin")->logout();
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        Event::dispatch('admin.session.logout.after' , $admin);
        return $this->successResponseWithMessage($this->lang("logout-success"));
    }
}
