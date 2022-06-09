<?php

namespace Modules\NavigationMenu\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Http\Controllers\BaseController;
use Modules\NavigationMenu\Entities\NavigationMenu;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Services\RedisHelper;
use Modules\NavigationMenu\Transformers\NavigationMenuResource;
use Modules\NavigationMenu\Repositories\NavigationMenuRepository;

class NavigationMenuController extends BaseController
{
     // Protected properties
     protected $repository, $redis_helper;

     /**
      * NavigationMenuController Class constructor
      */
     public function __construct(NavigationMenuRepository $navigationMenuRepository, NavigationMenu $navigationMenu, RedisHelper $redis_helper)
     {
         $this->repository = $navigationMenuRepository;

         $this->model = $navigationMenu;
         $this->redis_helper = $redis_helper;
         $this->model_name = "Navigation Menu";

         // Calling Parent Constructor of BaseController
         parent::__construct($this->model, $this->model_name);
     }

     /**
      * Returns NavigationMenuResource in Collection
      */
     public function collection(object $data): ResourceCollection
     {
         return NavigationMenuResource::collection($data);
     }

     /**
      * Returns NavigationMenuResource
      */
     public function resource(object $data): JsonResource
     {
         return new NavigationMenuResource($data);
     }

     /**
      * Fetches and returns the list of NavigationMenu
      */
     public function index(Request $request): JsonResponse
     {
         try
         {
            $fetched = $this->repository->fetchAll(request: $request, callback: function () use ($request) {
                return $this->repository->model()->whereWebsiteId($request->website_id);
            });
         }
         catch (Exception $exception)
         {
             return $this->handleException($exception);
         }

         return $this->successResponse($this->collection($fetched), $this->lang("fetch-list-success"));
     }

     /**
      * Validates and Creates NavigationMenu
      */
     public function store(Request $request): JsonResponse
     {
         try
         {
             $data = $this->repository->validateData($request, [
                "slug" => "string|min:2|max:250|unique:navigation_menus,slug",
             ]);

             $data = $this->repository->examineSlug($data);

             $created = $this->repository->createWithUniqueLocation($data);

             $this->redis_helper->deleteCache("store_front_nav_menu_website_*");
         }
         catch (Exception $exception)
         {
             return $this->handleException($exception);
         }

         return $this->successResponse($this->resource($created), $this->lang("create-success"), 201);
     }

     /**
      * Fetches and returns the NavigationMenu by Id
      */
     public function show(Request $request, int $id): JsonResponse
     {
         try
         {
             $fetched = $this->repository->fetch($id);
         }
         catch (Exception $exception)
         {
             return $this->handleException($exception);
         }

         return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
     }

     /**
      * Validates and Updates NavigationMenu
      */
     public function update(Request $request, int $id): JsonResponse
     {
         try
         {
            $data = $this->repository->validateData($request, [
                "slug" => "string|min:2|max:250|unique:navigation_menus,slug," . $id,
            ]);

            $data = $this->repository->examineSlug($data);

            $updated = $this->repository->updateWithUniqueLocation($data, $id);

            $this->redis_helper->deleteCache("store_front_nav_menu_website_*");
         }
         catch (Exception $exception)
         {
             return $this->handleException($exception);
         }

         return $this->successResponse($this->resource($updated), $this->lang("update-success"));
     }

     /**
      * Finds and Deletes NavigationMenu
      */
     public function destroy(Request $request, int $id): JsonResponse
     {
         try
         {
             $this->model->findOrFail($id);

             $this->repository->delete($id);

             $this->redis_helper->deleteCache("store_front_nav_menu_website_*");
         }
         catch (Exception $exception)
         {
             return $this->handleException($exception);
         }

         return $this->successResponseWithMessage($this->lang('delete-success'));
     }

     /**
      * Updates the Status of NavigationMenu with given Id
      */
     public function updateStatus(Request $request, int $id): JsonResponse
     {
         try
         {
            $updated = $this->repository->updateStatus($request, $id);

            $this->redis_helper->deleteCache("store_front_nav_menu_website_*");
         }
         catch (Exception $exception)
         {
             return $this->handleException($exception);
         }

         return $this->successResponse($this->resource($updated), $this->lang("status-updated"));
     }

}
