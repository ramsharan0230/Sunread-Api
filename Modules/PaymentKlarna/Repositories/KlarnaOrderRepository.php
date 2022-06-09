<?php

namespace Modules\PaymentKlarna\Repositories;

use Exception;
use Illuminate\Http\Request;
use Modules\Core\Services\Pipe;
use Modules\Sales\Entities\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Facades\SiteConfig;
use Illuminate\Support\Facades\Event;
use Modules\Sales\Entities\OrderComment;
use Modules\Sales\Facades\OrderStatusHelper;
use Modules\Cart\Repositories\CartRepository;
use Modules\Core\Repositories\BaseRepository;
use Modules\Sales\Traits\HasOrderCalculation;
use Modules\Sales\Traits\HasOrderProductDetail;
use Modules\Cart\Repositories\CartItemRepository;
use Modules\PaymentKlarna\Jobs\KlarnaOrderPushJob;
use Modules\Sales\Repositories\OrderMetaRepository;
use Modules\Customer\Repositories\CustomerRepository;
use Modules\CheckOutMethods\Services\BaseCheckOutMethods;
use Modules\CheckOutMethods\Traits\HasCheckOutHttpClient;
use Modules\Sales\Repositories\StoreFront\OrderRepository;
use Modules\PaymentAdyen\Exceptions\OrderNotFoundException;
use Modules\PaymentKlarna\Exceptions\PaymentKlarnaCheckoutIncompleteException;

class KlarnaOrderRepository extends BaseRepository
{
    use HasCheckOutHttpClient;
    use HasOrderProductDetail;
    use HasOrderCalculation;

    protected $orderRepository;
    protected $check_out_method_helper;
    protected $cartItemRepository;
    protected $customerRepository;

    public array $method_detail;
    protected string $method_key;
    public string $base_url;
    public array $headers;
    public $orderMetaRepository;

    public function __construct()
    {
        $this->model_key = "KlarnaOrder";
        $this->method_key = "klarna_orders";

        $this->rules = [
            "cart_id" => "required|exists:carts,id",
        ];

        $this->model = new Order();

        $this->orderRepository = new OrderRepository();
        $this->cartRepository = new CartRepository();
        $this->check_out_method_helper = new BaseCheckOutMethods();
        $this->cartItemRepository = new CartItemRepository();
        $this->customerRepository = new CustomerRepository();
        $this->orderMetaRepository = new OrderMetaRepository();

        $this->method_detail = [];
        $this->headers = ["accept" => "application/json"];
    }

    private function createBaseData($channelId = null): void
    {
        try
        {
            $this->user_name = SiteConfig::fetch("payment_methods_klarna_api_config_username", "channel", $channelId);
            $this->password = SiteConfig::fetch("payment_methods_klarna_api_config_password", "channel", $channelId);
            $this->method_detail = array_merge($this->method_detail, [
                "user_name" => $this->user_name,
                "password" =>  $this->password,
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    public function get(string $klarna_order_id): object
    {
        DB::beginTransaction();
        try
        {
            $order = $this->validateKlarnaOrderIdentifier($klarna_order_id);
            $klarna_get_order_response = $this->getClient("checkout/v3/orders/{$klarna_order_id}");
            $comment_data = [
                "order" => $order,
                "comment" => __("core::app.order_comment.api-initialized", ["name" => "klarna confirmation"]),
                "status_flag" => OrderComment::STATUS_INFO,
            ];
            Event::dispatch("order.comment.create.after", $comment_data);
            if (array_key_exists("status", $klarna_get_order_response) && !($klarna_get_order_response["status"] == "checkout_complete")) {
                $comment_data["comment"] = __("core::app.order_comment.confirmation-api-exception", ["name" => "klarna"]);
                $comment_data["status_flag"] = OrderComment::STATUS_ERROR;
                Event::dispatch("order.comment.create.after", $comment_data);
                throw new PaymentKlarnaCheckoutIncompleteException();
            }

            $comment_data["comment"] = __("core::app.order_comment.confirmation-api-response-received", ["name" => "klarna"]);
            $comment_data["status_flag"] = OrderComment::STATUS_SUCCESS;
            Event::dispatch("order.comment.create.after", $comment_data);

            KlarnaOrderPushJob::dispatchSync($order, (object) $klarna_get_order_response);
            $this->updateOrderStatus($klarna_order_id, $order);
            $this->cartRepository->delete($order->cart_id);
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return $order;
    }

    public function push(Request $request): void
    {
        DB::beginTransaction();
        try
        {
            $request->validate(["identifier" => "required"]);
            $order = $this->validateKlarnaOrderIdentifier($request->identifier);
            $comment_data = [
                "order" => $order,
                "comment" => __("core::app.order_comment.api-initialized", ["name" => "klarna webhook"]),
                "status_flag" => OrderComment::STATUS_INFO,
            ];
            Event::dispatch("order.comment.create.after", $comment_data);
            $this->updateOrderStatus($request->identifier, $order);
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
    }

    private function validateKlarnaOrderIdentifier(string $identifier): object
    {
        try
        {
            $klarna_order = $this->orderMetaRepository->query(function ($query) use ($identifier) {
                return $query->whereMetaKey("klarna_response")
                    ->whereJsonContains("meta_value->klarna_api_order_id", $identifier)
                    ->first();
            });
            if (!$klarna_order || !$klarna_order->order) {
                throw new OrderNotFoundException();
            }
            $order = $klarna_order->order;
            $this->createBaseData($order->channel_id);
            $this->base_url = $klarna_order->meta_value['base_url'];
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $order;
    }

    private function updateOrderStatus(string $klarna_order_identifier, object $order): void
    {
        try
        {
            $klarna_get_order_response = $this->getClient("ordermanagement/v1/orders/{$klarna_order_identifier}");
            $log_data = [
                "causer_type" => self::class,
                "order_id" => $order->id,
                "title" => __("core::app.response.order-status-updated"),
                "data" => $klarna_order_identifier,
                "action" => "{$this->model_key}.status.update",
            ];
            if (!array_key_exists("status", $klarna_get_order_response)) {
                $log_data["title"] = __("core::app.order_comment.confirmation-api-exception", ["name" => "klarna"]);
                Event::dispatch("order.create.update.after", $log_data);
                throw new OrderNotFoundException();
            }
            $order_status = $this->getOrderStatus((object) $klarna_get_order_response);
            if ($order->status != $order_status) {
                $updated_order = $this->orderRepository->update(["status" => $order_status], $order->id);
            }
            $comment_data = [
                "order" => $updated_order,
                "comment" => __("core::app.response.order-status-updated"),
                "status_flag" => OrderComment::STATUS_SUCCESS,
            ];
            Event::dispatch("order.comment.create.after", $comment_data);
            $this->captureOrder($klarna_order_identifier, (object) $klarna_get_order_response, $updated_order);

            $transaction_log = [
                "order" => $updated_order,
                "request" => $klarna_order_identifier,
                "response" => $klarna_get_order_response,
            ];
            Event::dispatch("order.create.update.after", $log_data);
            Event::dispatch("order.transaction.create.update.after", $transaction_log);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    private function captureOrder(string $klarna_order_identifier, object $klarna_response, object $order): void
    {
        try
        {
            $status = $klarna_response->status;
            $fraud_status = $klarna_response->fraud_status;
            $order_processing = ["AUTHORIZED", "CAPTURED"];
            $comment_data = ["order" => $order];
            if (in_array($status, $order_processing) && $fraud_status === "ACCEPTED") {
                $remaining_authorized_amount = $klarna_response->remaining_authorized_amount;
                if ($remaining_authorized_amount > 0) {
                    $taken = new Pipe($klarna_response);
                    $capture_processing_data = $taken->pipe($this->getOrderLines($taken->value))
                        ->pipe($this->capturePostData($taken->value->toArray(), $klarna_response))
                        ->value;

                    $comment_data["comment"] = __("core::app.order_comment.api-initialized", ["name" => "klarna capture"]);
                    $comment_data["status_flag"] = OrderComment::STATUS_INFO;
                    Event::dispatch("order.comment.create.after", $comment_data);

                    $this->postClient("ordermanagement/v1/orders/{$klarna_order_identifier}/captures", $capture_processing_data);

                    $comment_data["comment"] = __("core::app.order_comment.order-captured", ["name" => "klarna"]);
                    $comment_data["status_flag"] = OrderComment::STATUS_SUCCESS;
                    Event::dispatch("order.comment.create.after", $comment_data);

                    $log_data = [
                        "causer_type" => self::class,
                        "order_id" => $order->id,
                        "title" => "klarna order capture updated",
                        "data" => $klarna_order_identifier,
                        "action" => "{$this->model_key}.status.update",
                    ];
                    Event::dispatch("order.create.update.after", $log_data);
                }
            } else {
                $comment_data["comment"] = __("core::app.order_comment.order-captured-denied", ["name" => "klarna"]);
                $comment_data["status_flag"] = OrderComment::STATUS_ERROR;
                Event::dispatch("order.comment.create.after", $comment_data);
                $transaction_log = [
                    "order" => $order,
                    "request" => $klarna_order_identifier,
                    "response" => $klarna_response,
                ];
                Event::dispatch("order.transaction.create.update.after", $transaction_log);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    private function capturePostData(array $order_lines, object $klarna_response, ?callable $callback = null): array
    {
        try
        {
            $data = [
                "captured_amount" => $klarna_response->remaining_authorized_amount,
                "order_lines" => $order_lines,
                "reference" => $klarna_response->order_id,
                "shipping_info" => [
                    [
                        "shipping_method" => $klarna_response->selected_shipping_option["method"],
                    ],
                ],
            ];
            if ($callback) {
                $data = array_merge($data, $callback($data));
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    private function getOrderLines(object $klarna_response): Collection
    {
        try
        {
            $data = collect($klarna_response->order_lines)->map(function ($item) {
                $data = [
                    "name" => $item["name"],
                    "quantity" => $item["quantity"],
                    "reference" => $item["reference"],
                    "tax_rate" => $item["tax_rate"],
                    "total_amount" => $item["total_amount"],
                    "total_discount_amount" => $item["total_discount_amount"],
                    "total_tax_amount" => $item["total_tax_amount"],
                    "type" => $item["type"],
                    "unit_price" => $item["unit_price"],
                ];
                if ($item["type"] == "physical") {
                    $data["quantity_unit"] = $item["quantity_unit"];
                }
                return $data;
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    private function getOrderStatus(object $klarna_response): string
    {
        try
        {
            $status = $klarna_response->status;
            $fraud_status = $klarna_response->fraud_status;
            $order_processing = ["AUTHORIZED", "CAPTURED"];
            $order_cancel = ["CANCELLED", "EXPIRED", "CLOSED"];
            if (in_array($status, $order_processing) && $fraud_status === "ACCEPTED") {
                $order_status = OrderStatusHelper::getStateLatestOrderStatus("processing")->slug;
            } elseif (in_array($status, $order_cancel) || $status === "REJECTED" ) {
                $order_status = OrderStatusHelper::getStateLatestOrderStatus("cancelled")->slug;
            } else {
                $order_status = OrderStatusHelper::getStateLatestOrderStatus("pending")->slug;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $order_status;
    }
}