<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\Traits\ApiResponseFormat;

class ValidateChannelCode
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
        if(!$request->hasHeader('hc-channel')) 
        return $this->errorResponse("hc-channel is required", 422);

        return $next($request);
    }
}
