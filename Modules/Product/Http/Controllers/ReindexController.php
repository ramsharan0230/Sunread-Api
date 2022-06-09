<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Exception;
use Modules\Product\Jobs\ReindexMigrator;

class ReindexController extends BaseController
{
    public function __construct()
    {
    }

    public function reIndex(Request $request): JsonResponse
    {
        try
        {
            ReindexMigrator::dispatch()->onQueue("index");
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse([], __("core::app.response.reindex-success"));
    }
}
