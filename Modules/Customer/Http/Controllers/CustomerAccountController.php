<?php

namespace Modules\Customer\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Repositories\CustomerAddressRepository;
use Modules\Customer\Repositories\StoreFront\CustomerRepository;
use Modules\Customer\Transformers\CustomerAccountResource;

class CustomerAccountController extends BaseController
{
    protected $repository, $customerAddressRepository;

    public function __construct(CustomerRepository $customerRepository, Customer $customer, CustomerAddressRepository $customerAddressRepository)
    {
        $this->repository = $customerRepository;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->model = $customer;
        $this->model_name = "Customer";

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return CustomerAccountResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CustomerAccountResource($data);
    }

    public function show(): JsonResponse
    {
        try 
        {
            $fetched = auth()->guard("customer")->user();
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function update(Request $request): JsonResponse
    {
        try
        {
            $customer = auth()->guard("customer")->user();
            $data = $this->repository->updateAccount($request, $customer);      
            $updated = $this->repository->update($data, $customer->id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("update-success"));
    }

    public function uploadProfileImage(Request $request): JsonResponse
    {
        try
        {
            $updated = auth()->guard('customer')->user();
            $this->repository->removeOldImage($updated->id);
            $updated = $this->repository->uploadProfileImage($request, $updated->id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), "Profile image updated successfully.");
    }

    public function deleteProfileImage(): JsonResponse
    {
        try
        {
            $updated = auth()->guard('customer')->user();
            $updated = $this->repository->removeOldImage($updated->id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), "Profile image deleted successfully.");
    }
}
