<?php


namespace Modules\Core\Repositories;


use Modules\Core\Entities\ActivityLog;

class ActivityLogRepository extends BaseRepository
{
    public function __construct(ActivityLog $activityLog)
    {
        $this->model = $activityLog;
        $this->model_key = "core.activity";
        $this->rules = [
            "description" => "required",
        ];
    }
}
