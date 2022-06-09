<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\Traits\ApiResponseFormat;

class ValidateWebsiteHost
{
    use ApiResponseFormat;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try
        {
            if(!$request->hasHeader('hc-host'))
            return $this->errorResponse("hc-host is required", 422);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $next($request);
    }
}
