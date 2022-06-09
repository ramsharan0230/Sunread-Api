<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Core\Traits\FileManager;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Traits\ApiResponseFormat;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Core\Traits\Filterable;
use Modules\Core\Traits\HasExceptionHandler;
use Modules\Core\Traits\ResponseMessage;
use Modules\Core\Traits\Transformable;
use Modules\Core\Transformers\BaseResource;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller
{
    use ApiResponseFormat;
    use ValidatesRequests;
    use FileManager;
    use Filterable;
    use HasExceptionHandler;
    use ResponseMessage;
    use Transformable;

    protected string $locale;
    public $model;
    public $tarnsformer;
    public string $model_name;
    public array $exception_statuses;

    public function __construct(
        ?object $model,
        ?string $model_name,
        array $exception_statuses = []
    ) {

        $this->locale = config("locales.lang", config("app.locale"));
        $this->model = $model;
        $this->model_name = $model_name ?? str_replace("Controller", "", class_basename($this));
        $this->exception_statuses = array_merge($exception_statuses, [
            ModelNotFoundException::class => Response::HTTP_NOT_FOUND,
        ]);

        $this->transformer = $this->tarnsformer ?? new BaseResource(array());

    }

    public function storeImage(
        object $request,
        string $file_name,
        ?string $folder = null,
        ?string $delete_url = null
    ): ?string {
        try
        {
            // Check if file is given
            if ($request->file($file_name) !== null) {
                // Store File
                $file = $request->file($file_name);
                $key = Str::random(6);
                $folder = $folder ?? "default";
                $file_path = $file->storeAs("images/{$folder}/{$key}", $this->generateFileName($file));

                // Delete old file if requested
                if ($delete_url !== null) {
                    Storage::delete($delete_url);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $file_path ?? null;
    }

    public function handleException(object $exception): JsonResponse
    {
        return $this->errorResponse(
            message: $this->getExceptionMessage($exception),
            response_code: $this->getExceptionStatus($exception)
        );
    }

    public function generateFileName(object $file): string
    {
        try
        {
            $original_filename = $file->getClientOriginalName();
            $name = pathinfo($original_filename, PATHINFO_FILENAME);
            $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
            $filename_slug = Str::slug($name);
            $filename = "{$filename_slug}.{$extension}";
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return (string) $filename;
    }
}
