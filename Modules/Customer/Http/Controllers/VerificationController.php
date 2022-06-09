<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Entities\Customer;
use Exception;
use Modules\Core\Entities\Store;
use Modules\Customer\Repositories\StoreFront\CustomerRepository;

class VerificationController extends BaseController
{
    private $repository;

    public function __construct(Customer $customer, CustomerRepository $customerRepository)
    {
        $this->model = $customer;
        $this->model_name = "Customer";
        parent::__construct($this->model, $this->model_name);
        $this->repository = $customerRepository;
    }

    /**
     * customer send confirmation link to their email
    */
    public function sendConfirmation(): JsonResponse
    {
        try
        {
            $customer = auth()->guard('customer')->user();

            if(!$customer->is_email_verified) {
                $this->repository->sendVerificationLink($customer);
                $message = "response.send-confirmation-link";
            }
            else {
                $message = "response.already-verified";
            }
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang($message), 201);
    }

    /**
     * Customer verify their account
    */
    public function verifyAccount(string $channel_code, string $store_code, string $token): JsonResponse
    {
        try
        {
            CoreCache::getChannelWithCode($channel_code);
            Store::whereCode($store_code)->first();
            $customer = $this->model::where("verification_token", $token)->firstOrFail();

            if(!$customer->is_email_verified) {
                $this->repository->verifyCustomerAccount($customer);
                $message = "response.verification-success";
            }
            else {
                $message = "response.already-verified";
            }
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang($message), 201);
    }
}
