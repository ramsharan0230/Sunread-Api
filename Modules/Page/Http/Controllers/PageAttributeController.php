<?php

namespace Modules\Page\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Exception;
use Modules\Page\Entities\PageAttribute;
use Modules\Page\Repositories\PageAttributeRepository;

class PageAttributeController extends BaseController
{
    protected $repository;

    public function __construct(PageAttribute $pageAttribute, PageAttributeRepository $pageAttributeRepository)
    {
        $this->model = $pageAttribute;
        $this->model_name = "Page Attribute";
        $this->repository = $pageAttributeRepository;
        $exception_statuses = [
            PageNotFoundException::class => 404
        ];
        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->getComponents();
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-list-success'));
    }

    public function show(string $slug): JsonResponse
    {
        try
        {
            $fetched = $this->repository->show($slug);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }
}
