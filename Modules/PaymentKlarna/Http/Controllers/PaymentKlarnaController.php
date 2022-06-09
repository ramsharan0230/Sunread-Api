<?php

namespace Modules\PaymentKlarna\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Sales\Entities\Order;
use Modules\Sales\Transformers\OrderResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Sales\Repositories\StoreFront\OrderRepository;
use Modules\PaymentAdyen\Exceptions\OrderNotFoundException;
use Modules\PaymentKlarna\Repositories\KlarnaOrderRepository;
use Modules\PaymentKlarna\Exceptions\PaymentKlarnaCheckoutIncompleteException;

class PaymentKlarnaController extends BaseController
{
    protected $repository;
    protected $orderRepository;

    public function __construct()
    {
        $this->middleware('validate.website.host')->only(["confirm"]);
        $this->middleware('validate.channel.code')->only(["confirm"]);
        $this->middleware('validate.store.code')->only(["confirm"]);
        $this->model = new Order();
        $this->model_name = "KlarnaOrder";
        $this->repository = new KlarnaOrderRepository();
        $this->orderRepository = new OrderRepository();
        $this->exception_statuses = [
            PaymentKlarnaCheckoutIncompleteException::class => Response::HTTP_FORBIDDEN,
            OrderNotFoundException::class => Response::HTTP_NOT_FOUND,
        ];

        parent::__construct($this->model, $this->model_name, $this->exception_statuses);
    }

    public function resource(object $order): JsonResource
    {
        return new OrderResource($order);
    }

    public function confirm(string $klarna_order_id): JsonResponse
    {
        try
        {
            $order = $this->repository->get($klarna_order_id);
            $fetched = $this->orderRepository->fetch($order->id, $this->orderRepository->relations);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang("fetch-success"));
    }

    public function push(Request $request): JsonResponse
    {
        try
        {
            $this->repository->push($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang("fetch-success"));
    }
}
