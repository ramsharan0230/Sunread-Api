<?php

namespace Modules\PaymentAdyen\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class StatusUpdateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
           "message" => $this['message'],
           "result_code" => $this['result_code']
        ];
    }
}
