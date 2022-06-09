<?php

namespace Modules\Erp\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Erp\Entities\ErpPaymentMethodMapper;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Erp\Transformers\ErpPaymentMethodMapperResource;
use Modules\Erp\Repositories\PaymentMethodMapperRepository;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Modules\Erp\Transformers\PaymentMethodsMapperResource;

class PaymentMethodMapperController extends BaseController
{
    protected $repository;
    public $tarnsformer;

    public function __construct(ErpPaymentMethodMapper $paymentMethodMapper, PaymentMethodMapperRepository $paymentMethodMapperRepository)
    {
        $this->repository = $paymentMethodMapperRepository;
        $this->model = $paymentMethodMapper;
        $this->model_name = "Erp Payment Method Mapper";

        parent::__construct($this->model, $this->model_name);

        $this->transformer = new ErpPaymentMethodMapperResource(array());
    }

    public function paymentMethodsMapperCollection(object $data): ResourceCollection
    {
        return PaymentMethodsMapperResource::collection($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "website_id" => "required|exists:websites,id",
            ]);
            $fetched = $this->repository->fetchAll($request, callback: function () use ($request) {
                return $this->model->whereWebsiteId($request->website_id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->collection($fetched),
            message: $this->lang("fetch-list-success"),
        );
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback: function ($request) {
                return $request->validate([
                    "payment_method" => Rule::in($this->repository->getAllPaymentMethodsSlug()->pluck("slug")->toArray()),
                ]);
            });
            $created = $this->repository->create($data);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($created),
            message: $this->lang("create-success"),
            response_code: Response::HTTP_CREATED
        );
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($fetched),
            message: $this->lang("fetch-success")
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback: function ($request) use ($id) {
                return $request->validate([
                    "payment_method" => Rule::in($this->repository->getAllPaymentMethodsSlug()->pluck("slug")->toArray()),
                ]);
            });
            $updated = $this->repository->update($data, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($updated),
            message: $this->lang("update-success")
        );
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

        return $this->successResponseWithMessage(
            message: $this->lang("delete-success")
        );
    }

    public function getPaymentMethod(): JsonResponse
    {
        try
        {
            $data = $this->repository->getAllPaymentMethodsSlug();
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->paymentMethodsMapperCollection($data),
            message: $this->lang("fetch-success"),
            response_code: Response::HTTP_OK,
        );
    }
}
