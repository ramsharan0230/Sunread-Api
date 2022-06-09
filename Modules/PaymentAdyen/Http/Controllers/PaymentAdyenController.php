<?php

namespace Modules\PaymentAdyen\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Sales\Entities\Order;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cart\Exceptions\ChannelDoesNotExistException;
use Modules\Core\Http\Controllers\BaseController;
use Modules\PaymentAdyen\Exceptions\OrderNotFoundException;
use Modules\PaymentAdyen\Transformers\StatusUpdateResource;
use Modules\PaymentAdyen\Transformers\WebhookNotificationResource;
use Modules\PaymentAdyen\Repositories\AdyenPaymentStatusRepository;
use Modules\PaymentAdyen\Exceptions\PaymentAdyenCheckoutInCompleted;

class PaymentAdyenController extends BaseController
{
    protected $adyenPaymentStatusRepository;

    public function __construct(
        AdyenPaymentStatusRepository $adyenPaymentStatusRepository,
        Order $order
    ) {
        $this->middleware("validate.website.host")->except(["notificationWebhook"]);
        $this->middleware("validate.channel.code")->except(["notificationWebhook"]);
        $this->middleware("validate.store.code")->except(["notificationWebhook"]);

        $this->adyenPaymentStatusRepository = $adyenPaymentStatusRepository;
        $this->model = $order;
        $this->model_name = "Order";
        $exception_statuses = [
            InvalidAdyenNotificationRequest::class => Response::HTTP_FORBIDDEN,
            OrderNotFoundException::class => Response::HTTP_NOT_FOUND,
            PaymentAdyenCheckoutInCompleted::class => Response::HTTP_FORBIDDEN,
            ChannelDoesNotExistException::class => Response::HTTP_NOT_FOUND,
        ];

        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function statusUpdateResource(array $data): JsonResource
    {
        return new StatusUpdateResource($data);
    }

    public function webHookResource(array $data): JsonResource
    {
        return new WebhookNotificationResource($data);
    }

    public function updateOrderStatus(Request $request): JsonResponse
    {
        try
        {
            $response = $this->adyenPaymentStatusRepository->updateOrderStatus($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->statusUpdateResource($response),
            message: $this->lang("response.order-status-updated")
        );
    }

    public function paymentDetails(Request $request): JsonResponse
    {
        try
        {
            $response = $this->adyenPaymentStatusRepository->paymentDetails($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->resource($response),
            message: $this->lang("response.order-status-updated")
        );
    }

    public function notificationWebhook(Request $request): JsonResponse
    {
        try
        {
            $response = $this->adyenPaymentStatusRepository->notificationWebhook($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->webHookResource($response),
            message: null
        );
    }
}
