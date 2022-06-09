<?php

namespace App\Exceptions;

use finfo;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Modules\Core\Traits\ApiResponseFormat;
use Modules\Core\Traits\HasExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseFormat;
    use HasExceptionHandler;

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        Log::channel("log")
            ->error($exception->getMessage(), [
                "context" => [
                    "line" => $exception->getLine(),
                    "file" => $exception->getFile(),
                    // "trace" => $exception->getTraceAsString(),
                ]
            ]);
        if ($request->expectsJson()) {
            $this->exception_messages = [];
            $this->exception_statuses = [
                HttpException::class => method_exists($exception, "getStatusCode")
                    ? $exception->getStatusCode()
                    : Response::HTTP_INTERNAL_SERVER_ERROR,
            ];

            return $this->errorResponse(
                message: $this->getExceptionMessage($exception),
                response_code: $this->getExceptionStatus($exception)
            );
        } else {
            return parent::render($request, $exception);
        }
    }
}
