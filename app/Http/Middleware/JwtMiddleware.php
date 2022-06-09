<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Modules\Core\Traits\ApiResponseFormat;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{
    use ApiResponseFormat;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {

            JWTAuth::parseToken()->authenticate();

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $exception) {
            return $this->errorResponse(trans('core::app.users.auth.unauthenticated'), 401);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $exception) {
            return $this->errorResponse(trans('core::app.users.token.token-expired'), 401);
            
        }catch ( JWTException $exception){
            return $this->errorResponse(trans('core::app.users.auth.unauthenticated'), 401);

        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $next($request);
    }
}
