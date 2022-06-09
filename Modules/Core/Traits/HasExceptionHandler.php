<?php

namespace Modules\Core\Traits;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Modules\Core\Services\Pipe;
use Symfony\Component\HttpFoundation\Response;

trait HasExceptionHandler
{
    use ResponseMessage;

    public array $exception_statuses;

    public function getExceptionStatus(object $exception): int
    {
        $default_response_code = $exception->getCode() != 0
            ? $exception->getCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
        return array_merge([
            ValidationException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
            ModelNotFoundException::class => Response::HTTP_NOT_FOUND,
            QueryException::class => Response::HTTP_BAD_REQUEST,
            UnauthorizedException::class => Response::HTTP_UNAUTHORIZED,
            SlugCouldNotBeGenerated::class => Response::HTTP_INTERNAL_SERVER_ERROR,
            DeleteUnauthorized::class => Response::HTTP_UNAUTHORIZED,
            MethodException::class => Response::HTTP_INTERNAL_SERVER_ERROR,
        ], $this->exception_statuses)[get_class($exception)] ?? $default_response_code;
    }

   public function getExceptionMessage(object $exception): string
   {
        try
        {
            switch (get_class($exception)) {
                case ValidationException::class:
                    $exception_message = json_encode($exception->errors());
                break;

                case ModelNotFoundException::class:
                    $exception_message = $this->getModelNotFoundMessage($exception);
                break;

                case QueryException::class:
                    $exception_message = $this->getQueryExceptionMessage($exception);
                break;

                case UserNotDefinedException::class:
                    $exception_message = $this->lang("not-found", ["name" => "User"]);
                break;

                default:
                    $exception_message = $exception->getMessage();
                break;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $exception_message;
    }

    public function getModelNotFoundMessage(object $exception): string
    {
        $taken = new Pipe($exception->getModel());
        $model_name = $taken->pipe($this->getExceptionClass($taken->value))
                ->pipe($this->getExceptionClassPath($taken->value))
                ->pipe($this->getExceptionModelName($taken->value))
                ->value;
        return $this->lang("not-found", [ "name" => $model_name ]);
    }

    public function getExceptionClass(mixed $class): mixed
    {
        return explode('\\', $class);
    }

    public function getExceptionClassPath(mixed $path): mixed
    {
        return array_pop($path);
    }

    public function getExceptionModelName(mixed $model): mixed
    {
        return preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $model);
    }

    public function getQueryExceptionMessage(object $exception): mixed
    {
        return $exception->errorInfo[1] == 1062 ? "Duplicate Entry" : $exception->getMessage();
    }
}
