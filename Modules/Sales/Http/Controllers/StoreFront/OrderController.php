<?php

namespace Modules\Sales\Http\Controllers\StoreFront;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Sales\Entities\Order;
use Modules\Sales\Events\OrderCreated;
use Modules\Sales\Transformers\OrderResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Sales\Repositories\StoreFront\OrderRepository;
use Modules\Sales\Exceptions\BankTransferNotAllowedException;
use Modules\Sales\Exceptions\FreeShippingNotAllowedException;
use Modules\Sales\Exceptions\CashOnDeliveryNotAllowedException;
use Modules\Notification\Exceptions\EmailTemplateNotFoundException;

class OrderController extends BaseController
{
    protected $repository;
    protected $klarnaOrderRepository;
    protected $klarnaOrder;

    protected $relations = [
        "order_items.order",
        "order_taxes.order_tax_items",
        "website",
        "billing_address",
        "shipping_address",
        "customer",
        "order_status.order_status_state",
        "order_metas",
    ];

    public function __construct(
        Order $order,
        OrderRepository $repository,
    ) {
        $this->middleware('validate.website.host');
        $this->middleware('validate.channel.code');
        $this->middleware('validate.store.code');

        $this->model = $order;
        $this->model_name = "Order";
        $this->repository = $repository;

        $exception_statuses = [
            FreeShippingNotAllowedException::class => Response::HTTP_FORBIDDEN,
            BankTransferNotAllowedException::class => Response::HTTP_FORBIDDEN,
            CashOnDeliveryNotAllowedException::class => Response::HTTP_FORBIDDEN,
            UserNotDefinedException::class => Response::HTTP_NOT_FOUND,
            EmailTemplateNotFoundException::class => Response::HTTP_NOT_FOUND,
        ];

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function resource(mixed $data = []): JsonResource
    {
        return new OrderResource($data);
    }

    public function collection(object $orders): ResourceCollection
    {
        return OrderResource::collection($orders);
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->store($request);
            $response = $this->repository->fetch($fetched->id, $this->relations);
            $billing_address = $response->billing_address;
            if ($billing_address) {
                event(new OrderCreated($response));
            }
        }
        catch(Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($response),
            message: $this->lang('create-success')
        );
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, $this->relations, function () {
                return $this->model->whereCustomerId(auth("customer")->id());
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->collection($fetched),
            message: $this->lang('fetch-list-success')
        );
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, $this->relations);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($fetched),
            message: $this->lang('fetch-success')
        );
    }

    public function getShippingAndPaymentMethods(Request $request): JsonResponse
    {
        try
        {
            $method_lists = $this->repository->getMethodList($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $method_lists,
            message: $this->lang('fetch-list-success', ["name" => "Check Out"])
        );
    }

    public function fetchOrderDetail(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchOrderDetail($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($fetched),
            message: $this->lang('fetch-success')
        );
    }

}
