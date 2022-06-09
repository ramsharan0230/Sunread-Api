<?php

namespace Modules\Customer\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Facades\SiteConfig;
use Modules\Customer\Entities\Customer;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Customer\Transformers\CustomerResource;
use Modules\Customer\Repositories\CustomerRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Customer\Transformers\CustomerViewResource;
use Modules\Notification\Exceptions\EmailTemplateNotFoundException;

class CustomerController extends BaseController
{
    protected $repository;

    public function __construct(CustomerRepository $customerRepository, Customer $customer)
    {
        $this->repository = $customerRepository;
        $this->model = $customer;
        $this->model_name = "Customer";
        $exception_statuses = [
            EmailTemplateNotFoundException::class => Response::HTTP_NOT_FOUND,
        ];
        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function collection(object $data): ResourceCollection
    {
        return CustomerResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new CustomerResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, [ "group", "website", "store" ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang("fetch-list-success"));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, [
                "email" => [ "required", "email", Rule::unique('customers')->where(function ($query) use($request) {
                    $query->where('email', $request->email)
                       ->where('website_id', $request->website_id);
                })]
            ]);

            if(isset($data["password"])) $data["password"] = Hash::make($data["password"]);
            if(is_null($request->customer_group_id)) $data["customer_group_id"] = 1;

            if(SiteConfig::fetch("require_email_confirmation", "website", $request->website_id) == 1) {
                $data["verification_token"] = Str::random(30);
            }
            $created = $this->repository->create($data, function($created) {
                return $created->load("group", "website");
            });

            $this->repository->sendRegistrationEmail($created, $request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang("create-success"), 201);

    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, [ "group", "website", "store" ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang("fetch-success"));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, [
                "email" => [ "required", "email", Rule::unique('customers')->where(function ($query) use($request) {
                    $query->where('email', $request->email)
                       ->where('website_id', $request->website_id);
                })->ignore($id)]
            ]);

            if(isset($data["password"])) $data["password"] = Hash::make($data["password"]);
            if(is_null($request->customer_group_id)) $data["customer_group_id"] = 1;

            $updated = $this->repository->update($data, $id, function($updated) {
                return $updated->load("group", "website");
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("update-success"));

    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang("delete-success"));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try
        {
            $updated = $this->repository->updateStatus($request, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("status-updated"));
    }

    public function view(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, [ "group", "store", "addresses" ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(new CustomerViewResource($fetched), $this->lang("fetch-success"));
    }
}
