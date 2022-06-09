<?php

namespace Modules\Page\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Page\Entities\Page;
use Modules\Page\Repositories\PageRepository;
use Modules\Page\Transformers\PageResource;
use Exception;
use Modules\Page\Repositories\PageAttributeRepository;
use Modules\Page\Repositories\PageScopeRepository;
use Illuminate\Support\Str;

class PageController extends BaseController
{
    protected $repository, $pageScopeRepository, $pageAttributeRepository;

    public function __construct(Page $page, PageRepository $pageRepository, PageScopeRepository $pageScopeRepository, PageAttributeRepository $pageAttributeRepository)
    {
        $this->model = $page;
        $this->model_name = "Page";
        $this->repository = $pageRepository;
        $this->pageScopeRepository = $pageScopeRepository;
        $this->pageAttributeRepository = $pageAttributeRepository;
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return PageResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new PageResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "website_id" => "required|exists:websites,id"
            ]);
            $fetched = $this->repository->fetchAll($request, [ "page_scopes", "page_attributes", "website" ], function () use ($request) {
                return $this->model->whereWebsiteId($request->website_id);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback:function ($request) {
                return ["slug" => Str::slug($request->slug) ?? $this->model->createSlug($request->title)];
            });
            $this->repository->validateSlug($data);

            $created = $this->repository->create($data, function($created) use($data){
                if(isset($data["stores"])) $this->pageScopeRepository->updateOrCreate($data["stores"], $created);
                if(isset($data["components"])) $this->pageAttributeRepository->updateOrCreate($data["components"], $created);
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->show($id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback:function ($request) {
                return ["slug" => Str::slug($request->slug) ?? $this->model->createSlug($request->title)];
            });
            $this->repository->validateSlug($data, $id);

            $updated = $this->repository->update($data, $id, function($updated) use($data){
                if(isset($data["stores"])) $this->pageScopeRepository->updateOrCreate($data["stores"], $updated);
                if(isset($data["components"])) $this->pageAttributeRepository->updateOrCreate($data["components"], $updated, "update");
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('update-success'));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try
        {
            $updated = $this->repository->updateStatus($request, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('status-updated'));
    }
}
