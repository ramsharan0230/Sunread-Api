<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Facades\CoreCache;
use Modules\Customer\Entities\Customer;
use Illuminate\Support\Facades\Password;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Exceptions\TokenGenerationException;
use Modules\Customer\Exceptions\CustomerNotFoundException;
use Exception;
use Modules\Core\Entities\Store;
use Modules\Notification\Events\ResetPassword;

class ResetPasswordController extends BaseController
{
    public function __construct(Customer $customer)
    {
        $this->model = $customer;
        $this->model_name = "Password Reset";
        $exception_statuses = [
            TokenGenerationException::class => 401,
            CustomerNotFoundException::class => 404
        ];
        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function create(string $channel_code, string $store_code, ?string $token = null): JsonResponse
    {
        CoreCache::getChannelWithCode($channel_code);
        Store::whereCode($store_code)->first();
        return $token
            ? $this->successResponse(['token' => $token])
            : $this->errorResponse("Missing token", 400);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                "token" => "required",
                "email" => "required|email|exists:customers,email",
                "password" => "required|confirmed|min:6",
            ]);

            $response = $this->broker()->reset($data, function ($customer, $password) {
                $this->resetPassword($customer, $password);
            });

                if ($response == Password::INVALID_TOKEN) throw new TokenGenerationException($this->lang("token-generation-problem", []));
            if ($response == Password::INVALID_USER) throw new CustomerNotFoundException("Customer not found exception");

            $customer = $this->model::whereEmail($data["email"])->firstOrFail();

            event(new ResetPassword($customer));
        }

        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang("password-reset-success"));
    }

    protected function broker(): PasswordBroker
    {
        return Password::broker('customers');
    }

    protected function resetPassword(object $customer, string $password): void
    {
        $customer->password = Hash::make($password);
        $customer->setRememberToken(Str::random(60));
        $customer->save();
    }
}
