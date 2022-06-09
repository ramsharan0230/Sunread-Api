<?php

namespace Modules\PaymentAdyen\Repositories;

use Exception;
use Adyen\Util\HmacSignature;
use Modules\Cart\Entities\Cart;
use Modules\Sales\Entities\Order;
use Illuminate\Support\Facades\DB;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Facades\SiteConfig;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Modules\Sales\Entities\OrderComment;
use Modules\Sales\Traits\HasCheckoutUser;
use Modules\Sales\Facades\OrderStatusHelper;
use Modules\Cart\Repositories\CartRepository;
use Modules\Core\Repositories\BaseRepository;
use Modules\PaymentAdyen\Traits\AdyenApiConfiguration;
use Modules\Cart\Exceptions\ChannelDoesNotExistException;
use Modules\Sales\Repositories\StoreFront\OrderRepository;
use Modules\PaymentAdyen\Exceptions\InvalidAdyenNotificationRequest;
use Modules\PaymentAdyen\Exceptions\PaymentAdyenCheckoutInCompleted;
use Modules\Customer\Repositories\StoreFront\CustomerCheckoutRepository;

class AdyenPaymentStatusRepository extends BaseRepository
{
    use AdyenApiConfiguration;
    use HasCheckoutUser;

    private $cart;
    public $orderRepository;
    public $cartRepository;
    public $customerCheckoutRepository;

    public function __construct(
        Order $order,
        Cart $cart
    ) {
        $this->model = $order;
        $this->cart = $cart;
        $this->model_key = "orders";
        $this->orderRepository = new OrderRepository();
        $this->cartRepository = new CartRepository();
        $this->customerCheckoutRepository = new CustomerCheckoutRepository();
        $this->headers = [ "Accept" => "application/json" ];
        $this->urls = $this->getApiUrl();
        $this->rules = [
            "result_code" => "required",
            "order_id" => "required|exists:orders,id",
        ];
    }

    public function updateOrderStatus(object $request): ?array
    {
        DB::beginTransaction();
        try
        {
            $this->validateData($request);
            $data = $this->orderStatusUpdate($request->order_id, $request->result_code);
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return $data;
    }

    public function paymentDetails(object $request): mixed
    {
        DB::beginTransaction();
        try
        {
            $request->validate(["order_id" => "required|exists:orders,id"]);
            $coreCache = $this->getCoreCache($request);
            $this->baseData = $this->getBaseData($coreCache);
            $this->headers = array_merge($this->headers, [
                "Content-Type" => "application/json",
                "X-API-Key" => $this->baseData->api_key
            ]);
            $this->base_url = $this->getBaseUrl();
            $response = $this->postClient("v68/payments/details", $request->except(["order_id"]));
            if (empty($response)) {
                $order = $this->fetch($request->order_id);
                $transaction_log = [
                    "order" => $order,
                    "request" => $request,
                    "response" => __("core::app.response.adyen-payment-incomplete"),
                ];
                Event::dispatch("order.transaction.create.update.after", $transaction_log);
                throw new PaymentAdyenCheckoutInCompleted();
            }
            $data = $this->orderStatusUpdate(explode("|", $response["merchantReference"])[4], $response["resultCode"]);
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return $data;
    }

    public function notificationWebhook(object $request): array
    {
        DB::beginTransaction();
        try
        {
            $hmac = new HmacSignature();
            foreach ($request->notificationItems as $notificationRequestItem)
            {
                $params = $notificationRequestItem['NotificationRequestItem'];
                $channel = CoreCache::getChannelWithCode(explode("|", $params["merchantReference"])[2]);
                if (!$channel) {
                    throw new ChannelDoesNotExistException();
                }
                $hmacKey = SiteConfig::fetch("payment_methods_adyen_api_config_hmac_key", "channel", $channel->id);
                if (!$hmac->isValidNotificationHMAC($hmacKey, $params)) {
                    throw new InvalidAdyenNotificationRequest();
                }
                $orderId = explode("|", $params["merchantReference"])[4];
                $order = $this->fetch($orderId);
                $comment_data = [
                    "order" => $order,
                    "comment" => __("core::app.order_comment.api-initialized", ["name" => "adyen webhook"]),
                    "status_flag" => OrderComment::STATUS_INFO,
                ];
                Event::dispatch("order.comment.create.after", $comment_data);
                if ($params["eventCode"] === "AUTHORISATION") {
                    if ($params["success"] === "true") {
                        $status = OrderStatusHelper::getStateLatestOrderStatus("processing")->slug;
                        $this->update(["status" => $status], $orderId);
                        $comment_data["comment"] = __("core::app.response.order-status-updated");
                        $comment_data["status_flag"] = OrderComment::STATUS_SUCCESS;
                        Event::dispatch("order.comment.create.after", $comment_data);
                        $create_account = json_decode(Redis::get($order->cart_id));
                        if ($create_account) {
                            $this->createGuestUser($order);
                        }
                    } else {
                        $this->update(["status" => "cancelled"], $orderId);
                    }
                    $order = $this->fetch($order->id);
                    $transaction_log = [
                        "order" => $order,
                        "request" => "adyen",
                        "response" => __("core::app.response.order-status-updated"),
                    ];
                    $log_data = [
                        "causer_type" => self::class,
                        "order_id" => $order->id,
                        "title" => "adyen",
                        "data" => $request->all(),
                        "action" => "order.status.update",
                    ];
                    Event::dispatch("order.create.update.after", $log_data);
                    Event::dispatch("order.transaction.create.update.after", $transaction_log);
                }
            }

            $data = ["notification_response" =>"[accepted]"];
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return $data;
    }

    private function orderStatusUpdate(int $orderId, string $resultCode): array
    {
        try
        {
            $order = $this->fetch($orderId);
            $message = "";
            switch ($resultCode) {
                case "Authorised":
                    $cart = $this->cartRepository->queryFetch(["id" => $order->cart_id]);
                    $cart ? $cart->delete() : "";
                    $message = "payment is authorised";
                    break;
                case "Received":
                    $message = "payment received but yet to process";
                case "Refused":
                    $message = "payment is refused";
                case "Expired":
                case "Cancelled":
                case "Error":
                    $message = "payment is cancelled";
                    break;
                default:
                    $message = "something went wrong! payment is unsuccessful";
            }

            $transaction_log = [
                "order" => $order,
                "request" => $resultCode,
                "response" => $message,
            ];
            Event::dispatch("order.transaction.create.update.after", $transaction_log);
            $data = [
                "message" => $message,
                "result_code" => $resultCode,
            ];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }
}
