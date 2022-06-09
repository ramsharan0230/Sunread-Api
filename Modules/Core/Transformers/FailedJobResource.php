<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class FailedJobResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "connection" => $this->connection,
            "queue" => $this->queue,
            "payload" => json_decode($this->payload, true),
            "exception" => $this->exception,
        ];
    }
}
