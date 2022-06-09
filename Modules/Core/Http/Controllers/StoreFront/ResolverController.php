<?php

namespace Modules\Core\Http\Controllers\StoreFront;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Modules\Core\Entities\Website;
use Modules\Core\Exceptions\PageNotFoundException;
use Modules\Core\Facades\Resolver;
use Modules\Core\Http\Controllers\BaseController;

class ResolverController extends BaseController
{
    protected $repository;

    public function __construct(Website $website)
    {
        $this->model = $website;
        $this->model_name = "Website";
        $exception_statuses = [
            PageNotFoundException::class => 404
        ];
        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function resolve(Request $request): JsonResponse
    {
        try
        {
            $fetched = Resolver::fetch($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }
}
