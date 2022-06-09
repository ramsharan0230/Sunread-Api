<?php

namespace Modules\UrlRewrite\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\Traits\ApiResponseFormat;
use Modules\UrlRewrite\Contracts\UrlRewriteInterface;


class RewriteBaseController
{
    use ApiResponseFormat;
    /** @var UrlRewriteInterface */
    protected $repository;

    public function __construct(UrlRewriteInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(): object
    {
        $url = request()->path();
        $urlRewrite = $this->repository->getByRequestPath($url);
        if (!$urlRewrite) return $this->errorResponse("Page not found.", 404);
        if ($urlRewrite->isForward()) return $this->forwardResponse($urlRewrite->target_path);
        return redirect($urlRewrite->target_path, $urlRewrite->getRedirectType());
    }

    protected function forwardResponse($url)
    {
        return Route::dispatch(Request::create('/'.ltrim($url, '/'), request()->getMethod()));
    }
}
