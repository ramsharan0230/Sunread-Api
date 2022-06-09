<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Traits\ApiResponseFormat;

class ValidateErpToken
{
    use ApiResponseFormat;

    public function handle(Request $request, Closure $next): mixed
    {
        if (!($request->hasHeader("hc-api-key"))) {
            return $this->errorResponse("hc-api-key is required", 422);
        }
        if (($request->header("hc-api-key") !== config("erp_config.hc_api_key"))) {
            return $this->errorResponse(__("core::app.response.invalid-token"), 403);
        }
        return $next($request);
    }
}
