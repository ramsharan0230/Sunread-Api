<?php

namespace Modules\NavigationMenu\Http\Controllers\StoreFront;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\NavigationMenu\Entities\NavigationMenu;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\NavigationMenu\Repositories\NavigationMenuRepository;
use Modules\NavigationMenu\Repositories\StoreFront\NavigationMenuRepository as StorefrontNavigationMenuRepository;
use Modules\NavigationMenu\Transformers\StoreFront\NavigationMenuResource;
use Modules\NavigationMenu\Transformers\StoreFront\NavigationMenuShowResource;

class NavigationMenuController extends BaseController
{
    protected $repository;
    protected $storefrontNavigationMenuRepository;

    public function __construct(NavigationMenuRepository $navigationMenuRepository, NavigationMenu $navigationMenu)
    {
        $this->repository = $navigationMenuRepository;
        $this->storefrontNavigationMenuRepository = new StorefrontNavigationMenuRepository();
        $this->model = $navigationMenu;
        $this->model_name = "Navigation Menu";

        $this->middleware('validate.website.host');
        $this->middleware('validate.channel.code');
        $this->middleware('validate.store.code');

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return NavigationMenuResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new NavigationMenuResource($data);
    }

    public function showResource(object $data): JsonResource
    {
        return new NavigationMenuShowResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchItemsFromCache($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $fetched,
            message: $this->lang("fetch-list-success"),
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        try
        {
            $fetched = $this->storefrontNavigationMenuRepository->show($request, $slug);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse(
            payload: $this->showResource($fetched),
            message: $this->lang("fetch-list-success"),
        );
    }
}
