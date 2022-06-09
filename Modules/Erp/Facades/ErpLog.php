<?php

namespace Modules\Erp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static object webhookLog(int $website_id, string $entity_type, string $entity_id, array $payload, ?bool $is_processing = true, ?bool $status = true)
 * @method static void erpLog(object $order, int|string $entity_id, string $event, string $type, array $request, array $response, ?string $causer_type = "system", ?string $entity_type = "order", ?int $response_code = Response::HTTP_OK)
 * @method static object orderComment(\Modules\Sales\Entities\Order $order, string $comment, ?string $status_flag = OrderComment::STATUS_INFO)
 */
class ErpLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ErpLog';
    }
}