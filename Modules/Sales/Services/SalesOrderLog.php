<?php

namespace Modules\Sales\Services;
use Exception;
use Modules\Sales\Entities\OrderComment;
use Modules\Sales\Repositories\OrderLogRepository;
use Modules\Sales\Repositories\OrderCommentRepository;
use Modules\Sales\Repositories\OrderTransactionLogRepository;

class SalesOrderLog
{
    protected $orderLogRepository;
    protected $orderTransactionRepository;
    protected $orderCommentRepository;

    public function __construct()
    {
        $this->orderLogRepository = new OrderLogRepository();
        $this->orderTransactionRepository = new OrderTransactionLogRepository();
        $this->orderCommentRepository = new OrderCommentRepository();
    }

    public function orderLog(
        string $causer_type,
        int $order_id,
        string $title,
        mixed $data,
        string $action,
        ?int $causer_id = null
    ): void {
        try
        {
            $data = [
                "causer_type" => $causer_type,
                "causer_id" => $causer_id,
                "order_id" => $order_id,
                "title" => $title,
                "data" => json_encode($data),
                "action" => $action,
            ];
            $this->orderLogRepository->create($data);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    public function transactionLog(
        object $order,
        mixed $request,
        mixed $response,
        int $response_code
    ): void {
        try
        {
            $this->orderTransactionRepository->create([
                "order_id" => $order->id,
                "amount" => $order->grand_total,
                "currency" => $order->currency_code,
                "ip_address" => $order->customer_ip_address,
                "request" => json_encode($request),
                "response" => json_encode($response),
                "response_code" => $response_code,
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }

    public function createOrderComment(
        object $order,
        ?string $comment = null,
        ?string $status_flag = OrderComment::STATUS_INFO,
        ?int $is_customer_notified = 0,
        ?int $is_visible_on_storefront = 0
    ): void {
        try
        {
            if (!$comment) {
                $comment =  $order->wasRecentlyCreated
                    ? __("core::app.response.create-success", ["name" => "order"])
                    : __("core::app.response.update-success", ["name" => "order"]);
            }
            $data = [
                "order_id" => $order?->id,
                "user_id" => auth("admin")->id(),
                "comment" => $comment ?? __("core::app.order_comment.create-update-success", ["name" => "order"]),
                "status_flag" => $status_flag,
                "is_customer_notified" => $is_customer_notified,
                "is_visible_on_storefornt" => $is_visible_on_storefront,
            ];
            $this->orderCommentRepository->create($data);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }
}
