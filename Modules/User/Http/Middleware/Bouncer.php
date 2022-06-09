<?php

namespace Modules\User\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Core\Traits\ApiResponseFormat;

class Bouncer
{
    use ApiResponseFormat;
    
    public function handle(object $request, Closure $next, string $guard = 'admin'): mixed
    {
        if (!Auth::guard($guard)->check()) return $this->errorResponse("Unauthenticated User.", 401);
        if (!$this->checkIfAuthorized($request)) return $this->errorResponse("Unauthorised access.", 403);

        return $next($request);
    }

    public function checkIfAuthorized(object $request): bool
    {
        if ( !($role = auth()->guard('admin')->user()->role) ) return false;
        if ($role->permission_type == 'all') return true;

        return bouncer()->allow(Route::currentRouteName());
    }
}
