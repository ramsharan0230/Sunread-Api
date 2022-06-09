<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class JobTrackerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "total_jobs" => $this->total_jobs,
            "completed_jobs" => $this->completed_jobs,
            "failed_jobs" => $this->failed_jobs,
            "percentage" => (($this->completed_jobs + $this->failed_jobs) / $this->total_jobs) * 100,
            "status" => ($this->status == 1) ? "completed" : "running"
        ];
    }
}
