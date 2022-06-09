<?php

namespace Modules\Core\Services;

use Modules\Core\Entities\JobTracker;
use Modules\Core\Entities\Store;
use Modules\Product\Entities\Product;

class JobTrackerHelper {

    protected $model;

    public function __construct(JobTracker $job_tracker)
    {
        $this->model = $job_tracker;
    }

    public function complete()
    {
        $this->updateJob(1);
    }

    public function fail()
    {
        $this->updateJob(0);
    }

    public function updateJob(int $check)
    {
        $job_tracker = $this->model->whereName("reindex")->whereStatus(0)->first();
        
        if($job_tracker) {
            $product_count = Product::count();
            $store_count = Store::count();
            $data = [
                "total_jobs" => ($product_count * $store_count) + 2,
                "completed_jobs" => ($check == 1) ? $job_tracker->completed_jobs + 1 : $job_tracker->completed_jobs,
                "failed_jobs" => ($check == 0) ? $job_tracker->failed_jobs + 1 : $job_tracker->failed_jobs
            ];
            $job_tracker->fill($data);
            $job_tracker->save();
                
            if($job_tracker->total_jobs == ( $job_tracker->completed_jobs + $job_tracker->failed_jobs )) {
                $job_tracker->status = 1;
                $job_tracker->save();
            }
        }
    }
}