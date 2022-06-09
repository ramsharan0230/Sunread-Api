<?php

namespace Modules\NavigationMenu\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Core\Rules\ScopeRule;
use Modules\Core\Services\RedisHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Repositories\WebsiteRepository;
use Modules\Core\Http\Controllers\BaseController;
use Modules\NavigationMenu\Entities\NavigationMenu;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\NavigationMenu\Entities\NavigationMenuItem;
use Modules\NavigationMenu\Rules\NavigationMenuItemScopeRule;
use Modules\NavigationMenu\Repositories\NavigationMenuRepository;
use Modules\NavigationMenu\Transformers\NavigationMenuItemResource;
use Modules\NavigationMenu\Transformers\List\NavigationMenuItemResource as NavigationMenuItemResourceList;
use Modules\NavigationMenu\Repositories\NavigationMenuItemRepository;
use Modules\NavigationMenu\Exceptions\NavigationMenuItemNotFoundException;
use Modules\NavigationMenu\Repositories\NavigationMenuItemValueRepository;

class NavigationMenuItemController extends BaseController
{
    // Protected properties
    protected $repository, $navigation_menu, $navigation_menu_repository, $navigation_menu_item_value_repository, $redis_helper;

    /**
     * NavigationMenuItemController Class constructor
     */
    public function __construct(
        NavigationMenuItemRepository $navigation_menu_item_repository,
        NavigationMenuItem $navigation_menu_item,
        NavigationMenuRepository $navigation_menu_repository,
        NavigationMenu $navigation_menu,
        NavigationMenuItemValueRepository $navigation_menu_item_value_repository,
        RedisHelper $redisHelper,
        WebsiteRepository $websiteRepository
    )
    {
        $this->repository = $navigation_menu_item_repository;
        $this->navigation_menu_item_value_repository = $navigation_menu_item_value_repository;
        $this->navigation_menu = $navigation_menu;
        $this->navigation_menu_repository = $navigation_menu_repository;
        $this->redis_helper = $redisHelper;
        $this->website_repository = $websiteRepository;


        $this->model = $navigation_menu_item;
        $this->model_name = "Navigation Menu Item";

        // Calling Parent Constructor of BaseController
        parent::__construct($this->model, $this->model_name);
    }

    /**
     * Returns NavigationMenuItemResource Collection
     */
    public function collection(object $data): ResourceCollection
    {
        return NavigationMenuItemResource::collection($data);
    }

    /**
     * Returns NavigationMenuItemResource
     */
    public function resource(object $data): JsonResource
    {
        return new NavigationMenuItemResource($data);
    }

    /**
     * Returns NavigationMenuItemResource in List Collection
     */
    public function list(object $data): ResourceCollection
    {
        return NavigationMenuItemResourceList::collection($data);
    }

    /**
     * Fetches and returns the list of NavigationMenuItem
     */
    public function index(Request $request, int $navigation_menu_id): JsonResponse
    {
        try
        {
            $request->validate([
                "scope" => "sometimes|in:website,channel,store",
                "scope_id" => [ "sometimes", "integer", "min:1", new ScopeRule($request->scope), new NavigationMenuItemScopeRule($request)],
            ]);

            $this->navigation_menu->findOrFail($navigation_menu_id);

            $fetched = $this->repository->fetchAll($request, ["values", "navigationMenu"], function() use($navigation_menu_id){
                return $this->model->where('navigation_menu_id', $navigation_menu_id)->orderBy("_lft", "asc");
            })->toTree();
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->list($fetched), $this->lang("fetch-list-success"));
    }

    /**
     * Validates and Creates NavigationMenuItem with NavigationMenuItemValue
     */
    public function store(Request $request, int $navigation_menu_id): JsonResponse
    {
        try
        {
            $navigation_menu = $this->navigation_menu_repository->fetch($navigation_menu_id);

            $data = $this->navigation_menu_item_value_repository->validateWithValuesCreate($request, $navigation_menu->website_id);

            $data = array_merge($data, ['navigation_menu_id' => $navigation_menu_id]);

            $created = $this->repository->create($data, function ($created) use ($data) {
                $this->navigation_menu_item_value_repository->createOrUpdate($data, $created);
            });

            $website = $this->website_repository->fetch($navigation_menu->website_id);

            $this->redis_helper->deleteCache("store_front_nav_menu_website_{$website->hostname}_*");

        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang("create-success"), 201);
    }

    /**
     * Fetches and returns the NavigationMenuItem by Id
     */
    public function show(Request $request, int $navigation_menu_id, int $id): JsonResponse
    {
        try
        {
            $request->validate([
                "scope" => "sometimes|in:website,channel,store",
                "scope_id" => [ "sometimes", "integer", "min:1", new ScopeRule($request->scope), new NavigationMenuItemScopeRule($request, $id)]
            ]);

            $fetched = $this->repository->fetchWithAttributes($request, $id);

            if($fetched["navigation_menu_id"] !== $navigation_menu_id) throw new NavigationMenuItemNotFoundException();
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }

    /**
     * Validates and Updates NavigationMenuItem with NavigationMenuItem values
     */
    public function update(Request $request, int $navigation_menu_id, int $id): JsonResponse
    {
        try
        {
            $navigation_menu_item = $this->repository->fetch($id, ["navigationMenu"]);

            if($navigation_menu_item->navigation_menu_id !== $navigation_menu_id) throw new NavigationMenuItemNotFoundException();

            $data = $this->navigation_menu_item_value_repository->validateWithValuesUpdate($request, $navigation_menu_item);

            $updated = $this->repository->update($data, $id, function ($updated) use ($data) {
                $this->navigation_menu_item_value_repository->createOrUpdate($data, $updated);
                $updated->load("values");
            });

            $website = $this->website_repository->fetch($navigation_menu_item->navigationMenu->website_id);

            $this->redis_helper->deleteCache("store_front_nav_menu_website_{$website->hostname}_*");

        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("update-success"));
    }

    /**
     * Finds and Deletes NavigationMenuItem
     */
    public function destroy(Request $request, int $navigation_menu_id, int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, ['navigationMenu']);

            if($fetched->navigation_menu_id !== $navigation_menu_id) throw new NavigationMenuItemNotFoundException();

            $this->repository->delete($id);

            $website = $this->website_repository->fetch($fetched->navigationMenu->website_id);

            $this->redis_helper->deleteCache("store_front_nav_menu_website_{$website->hostname}_*");
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    /**
     * Updates the Status of NavigationMenuItem with given Id
     */
    public function updateStatus(Request $request, int $navigation_menu_id, int $id): JsonResponse
    {
        try
        {
            $navigation_menu = $this->navigation_menu_repository->fetch($navigation_menu_id);

            $data = $request->validate([
                "items.status.value" => "required|numeric",
                "scope" => "required|in:website,channel,store",
                "scope_id" => [ "required", "integer", "min:1", new ScopeRule($request->scope), new NavigationMenuItemScopeRule($request, $id)]
            ]);


            $updated = $this->repository->update($data, $id, function ($updated) use ($data) {
                $this->navigation_menu_item_value_repository->createOrUpdate($data, $updated);
                $updated->load("values");
            });

            $website = $this->website_repository->fetch($navigation_menu->website_id);
            $this->redis_helper->deleteCache("store_front_nav_menu_website_{$website->hostname}_*");
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang("status-updated"));
    }

    public function updatePosition(Request $request, int $navigation_menu_id, int $id): JsonResponse
    {
        try
        {
            $navigation_menu_item = $this->repository->updatePosition($request, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($navigation_menu_item), $this->lang("update-success"));
    }

    /**
     * Fetches and returns Attributes for NavigationMenuItem Values
     */
    public function attributes(Request $request): JsonResponse
    {
        try
        {
            $request->validate([
                "scope" => "sometimes|in:website,channel,store"
            ]);

            $fetched = $this->repository->getConfigData([
                "scope" => $request->scope ?? "website"
            ]);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang("fetch-success"));
    }

    /**
     * Fetches and returns Attributes for NavigationMenuItem Values
     */
    public function locations(): JsonResponse
    {
        try
        {
            $fetched = $this->repository->getLocationData();
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang("fetch-success"));
    }
}
