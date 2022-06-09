<?php

namespace Modules\PaymentAdyen\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class WebhookNotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
           "notification_response" => $this['notification_response'],
        ];
    }
}
