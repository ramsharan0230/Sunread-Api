<?php

namespace Modules\Erp\Services;

use Exception;
use Modules\Erp\Repositories\ErpLogRepository;
use Modules\Erp\Repositories\ErpWebhookLogRepository;
use Modules\Sales\Entities\Order;
use Modules\Sales\Entities\OrderComment;
use Modules\Sales\Repositories\OrderCommentRepository;
use Symfony\Component\HttpFoundation\Response;

class ErpLog
{
    protected $webhookRepository;
    protected $erpLogRepository;
    protected $orderCommentRepository;

    public function __construct()
    {
        $this->webhookRepository = new ErpWebhookLogRepository();
        $this->erpLogRepository = new ErpLogRepository();
        $this->orderCommentRepository = new OrderCommentRepository();
    }

    public function webhookLog(
        int $website_id,
        string $entity_type,
        string $entity_id,
        array $payload,
        ?bool $is_processing = true,
        ?bool $status = true
    ): object {
        try
        {
            $data = [
                "website_id" => $website_id,
                "entity_type" => "product.{$entity_type}",
                "entity_id" => $entity_id,
                "payload" => json_encode($payload),
                "is_processing" => $is_processing,
                "status" => $status,
            ];
            $webhook = $this->webhookRepository->create($data);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $webhook;
    }

    public function erpLog(
        object $order,
        int|string $entity_id,
        string $event,
        string $type,
        array $request,
        array $response,
        ?string $causer_type = "system",
        ?string $entity_type = "order",
        ?int $response_code = Response::HTTP_OK
    ): void {
        try
        {
            $this->erpLogRepository->create([
                "website_id" => $order->website->id,
                "entity_id" => $entity_id,
                "entity_type" => $entity_type,
                "causer_type" => $causer_type,
                "causer_id" => auth("admin")->user()->id ?? null,
                "event" => $event,
                "resoponse_code" => $response_code,
                "request" => json_encode($request),
                "response" => json_encode($response),
                "type" => $type,
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function orderComment(Order $order, string $comment, ?string $status_flag = OrderComment::STATUS_INFO): object
    {
        try
        {
            $orderComment = $this->orderCommentRepository->create([
                "order_id" => $order->id,
                "comment" => $comment,
                "status_flag" => $status_flag,
            ]);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $orderComment;
    }
}