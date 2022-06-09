<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "log_name" => $this->log_name,
            "description" => $this->description,
            "action" => $this->action,
            "activity" => $this->activity,
            "subject" => $this->whenLoaded("subject"),
            "causer" => $this->whenLoaded("causer"),
            "properties" => $this->properties,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
