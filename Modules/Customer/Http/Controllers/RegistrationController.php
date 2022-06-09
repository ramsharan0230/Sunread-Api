<?php

namespace Modules\Customer\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Customer\Entities\Customer;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Transformers\CustomerResource;
use Modules\Customer\Repositories\StoreFront\CustomerRepository;
use Modules\Notification\Exceptions\EmailTemplateNotFoundException;

class RegistrationController extends BaseController
{
    protected $repository;

    public function __construct(CustomerRepository $customerRepository, Customer $customer)
    {
        $this->repository = $customerRepository;
        $this->model = $customer;
        $this->model_name = "Customer Registration";
        $exception_statuses = [
            EmailTemplateNotFoundException::class => Response::HTTP_NOT_FOUND,
        ];
        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function resource(object $data): JsonResource
    {
        return new CustomerResource($data);
    }

    public function register(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->registration($request);
            $data["is_email_verified"] = 1;
            $created = $this->repository->create($data);

            $this->repository->sendRegistrationEmail($created, $request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }
}
