<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Modules\Core\Http\Controllers\BaseController;
use Exception;
use Modules\Customer\Entities\Customer;

/**
 * Session Controller for the Customer
 * @author    Hemant Achhami
 * @copyright 2020 Hazesoft Pvt Ltd
 */
class SessionController extends BaseController
{
    public function __construct(Customer $customer)
    {
        $this->middleware('guest:customer')->except(['logout']);

        $this->model = $customer;
        $this->model_name = "Customer account";

        parent::__construct($this->model, $this->model_name);
    }

    public function login(Request $request)
    {
        Event::dispatch('customers.session.login.before');

        try
        {
            $data = $request->validate([
                "email" => "required|email",
                "password" => "required"
            ]);

            $jwtToken = Auth::guard("customer")
                ->setTTL(config("jwt.customer_jwt_ttl")) // Customer's JWT token time to live
                ->attempt($data);

            if (!$jwtToken) return $this->errorResponse($this->lang("login-error"), 401);

            $payload = [
                "token" => $jwtToken,
                "user" => auth()->guard("customer")->user()
            ];
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        Event::dispatch('customers.session.login.after', auth()->guard("customer")->user());
        return $this->successResponse($payload, $this->lang("login-success"));
    }

    /**
     * Logout the Customer
     *
     * @return JsonResponse
     */
    public function logout()
    {
        Event::dispatch('customers.session.logout.before');

        try
        {
            $customer = auth()->guard('customer')->user();
            auth()->guard("customer")->logout();
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        Event::dispatch('customers.session.logout.after' , $customer);
        return $this->successResponseWithMessage($this->lang("logout-success"));
    }
}
