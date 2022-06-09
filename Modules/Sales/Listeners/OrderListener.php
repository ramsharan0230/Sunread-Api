<?php

namespace Modules\Sales\Listeners;

use Exception;
use Modules\Sales\Entities\OrderComment;
use Modules\Sales\Facades\SalesOrderLog;
use Symfony\Component\HttpFoundation\Response;

class OrderListener
{
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
            SalesOrderLog::orderLog(
                causer_type: $causer_type,
                order_id: $order_id,
                title: $title,
                data: $data,
                action: $action,
                causer_id: $causer_id
            );
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
        int $response_code = Response::HTTP_OK
    ): void {
        try
        {
            SalesOrderLog::transactionLog(
                order: $order,
                request: $request,
                response: $response,
                response_code: $response_code
            );
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
        int $is_customer_notified = 0,
        int $is_visible_on_storefront = 0,
    ): void {
        try
        {
            SalesOrderLog::createOrderComment(
                order: $order,
                comment: $comment,
                status_flag: $status_flag,
                is_customer_notified: $is_customer_notified,
                is_visible_on_storefront: $is_visible_on_storefront
            );
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

    }
}
