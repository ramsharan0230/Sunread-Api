<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Entities\JobTracker;
use Modules\Core\Transformers\JobTrackerResource;

class JobTrackerController extends BaseController
{

    public function __construct(JobTracker $jobTracker)
    {
        $this->model = $jobTracker;
        $this->model_name = "Job Tracker";

        parent::__construct($this->model, $this->model_name);
    }

    public function resource(object $data): JsonResource
    {
        return new JobTrackerResource($data);
    }

    public function show(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->model->whereName("reindex")->whereStatus(0)->orderBy("created_at", "desc")->first();
            if(!$fetched) {
                $fetched = $this->model->whereName("reindex")->whereStatus(1)->orderBy("created_at", "desc")->firstorFail();
            }
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-list-success'));
    }


}
