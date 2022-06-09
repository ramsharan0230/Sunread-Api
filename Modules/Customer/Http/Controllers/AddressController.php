<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Entities\Website;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerAddress;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Transformers\CustomerAddressResource;
use Modules\Customer\Repositories\CustomerAddressRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Customer\Repositories\CustomerRepository;
use Modules\Customer\Transformers\CustomerDefaultAddressResource;

class AddressController extends BaseController
{
    protected $repository, $customerRepository;

    public function __construct(CustomerAddressRepository $customerAddressRepository, CustomerAddress $customer_address, CustomerRepository $customerRepository)
    {
        $this->repository = $customerAddressRepository;
        $this->model = $customer_address;
        $this->model_name = "Customer Address";
        $this->customerRepository = $customerRepository;

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return CustomerAddressResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CustomerAddressResource($data);
    }

    public function defaultAddressResource(object $data): JsonResource
    {
        return new CustomerDefaultAddressResource($data);
    }

    public function index(Request $request, int $customer_id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, callback: function() use ($customer_id) {
                return $this->model::whereCustomerId($customer_id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function store(Request $request, int $customer_id): JsonResponse
    {
        try
        {
            $customer = Customer::findOrFail($customer_id);
            $channel_id = $this->repository->checkCustomerChannel($request, $customer);
            $data = $this->repository->validateData($request, $this->repository->regionAndCityRules($request), function () use ($customer_id, $channel_id) {
                return [
                    "customer_id" => $customer_id,
                    "channel_id" => $channel_id
                ];
            });
            $data = $this->repository->checkCountryRegionAndCity($data, $channel_id);

            $created = $this->repository->create($data, function($created) use ($data, $customer_id) {
                $this->repository->unsetDefaultAddresses($data, $customer_id, $created->id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $customer_id, int $address_id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($address_id, callback: function() use ($customer_id) {
                return $this->model::whereCustomerId($customer_id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function update(Request $request, int $customer_id, int $address_id): JsonResponse
    {
        try
        {
            $customer = Customer::findOrFail($customer_id);
            $channel_id = $this->repository->checkCustomerChannel($request, $customer);
            $data = $this->repository->validateData($request, $this->repository->regionAndCityRules($request), function () use ($customer_id, $channel_id) {
                return [
                    "customer_id" => $customer_id,
                    "channel_id" => $channel_id
                ];
            });
            $data = $this->repository->checkCountryRegionAndCity($data, $channel_id);

            $updated = $this->repository->update($data, $address_id, function($updated) use ($data, $customer_id) {
                $this->repository->unsetDefaultAddresses($data, $customer_id, $updated->id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('update-success'));
    }

    public function destroy(int $customer_id, int $address_id): JsonResponse
    {
        try
        {
            $this->repository->delete($address_id, function($deleted) use ($customer_id) {
                if ( $deleted->customer_id != $customer_id ) throw new ModelNotFoundException($this->lang("not-found"));
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    public function updateAddress(Request $request, int $customer_id, int $address_id)
    {
        try
        {
            $data = $request->validate([
                "default_billing_address" => "required_without:default_shipping_address|boolean",
                "default_shipping_address" => "required_without:default_billing_address|boolean",
            ]);

            $updated = $this->repository->updateDefaultAddress($data, $customer_id, $address_id, function($updated) use ($data, $customer_id) {
                $this->repository->unsetDefaultAddresses($data, $customer_id, $updated->id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('update-success'));
    }

    public function default(int $customer_id): JsonResponse
    {
        try
        {
            $fetched = $this->customerRepository->fetch($customer_id, ["addresses"]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->defaultAddressResource($fetched), $this->lang('fetch-success'));
    }
}
