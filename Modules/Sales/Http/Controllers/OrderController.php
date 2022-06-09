<?php

namespace Modules\Sales\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Sales\Entities\Order;
use Modules\Sales\Events\OrderStatusUpdated;
use Modules\Sales\Transformers\OrderResource;
use Symfony\Component\HttpFoundation\Response;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Sales\Repositories\OrderStatusStateRepository;
use Modules\Sales\Repositories\StoreFront\OrderRepository;
use Modules\Sales\Repositories\OrderStatusUpdateRepository;
use Modules\Notification\Exceptions\EmailTemplateNotFoundException;

class OrderController extends BaseController
{
    protected $repository;
    protected $orderStatusUpdateRepository;
    protected $orderStatusState;

    protected array $relations = [
        "order_items.order",
        "order_taxes.order_tax_items",
        "website",
        "billing_address",
        "shipping_address",
        "customer",
        "order_status.order_status_state",
    ];

    public function __construct(
        OrderRepository $repository,
        Order $order,
        OrderStatusUpdateRepository $orderStatusUpdateRepository
    ) {
        $this->model = $order;
        $this->model_name = "Order";
        $this->repository = $repository;
        $this->orderStatusUpdateRepository = $orderStatusUpdateRepository;
        $this->orderStatusState = new OrderStatusStateRepository();
        $exception_statuses = [
            EmailTemplateNotFoundException::class => Response::HTTP_NOT_FOUND,
        ];
        parent::__construct($this->model, $this->model_name, $exception_statuses);
        $this->transformer = new OrderResource(array());
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, $this->relations);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->collection($fetched),
            message: $this->lang("fetch-list-success")
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
            message: $this->lang("fetch-success")
        );
    }

    public function orderStatus(Request $request): JsonResponse
    {
        try
        {
            $this->orderStatusUpdateRepository->validateData($request);
            $order = $this->repository->fetch($request->order_id);
            $status = $this->orderStatusUpdateRepository->validateOrderStatusState($request->order_status_id, $order);
            $order = $this->repository->update(["status" => $status->slug], $request->order_id, function ($order) {
                event(new OrderStatusUpdated($order));
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($order),
            message: $this->lang("update-success")
        );
    }
}
