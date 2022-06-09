<?php

namespace Modules\NavigationMenu\Repositories\StoreFront;

use Exception;
use Illuminate\Http\Request;
use Modules\Core\Facades\CoreCache;
use Modules\Core\Repositories\BaseRepository;
use Modules\NavigationMenu\Entities\NavigationMenu;

class NavigationMenuRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new NavigationMenu();
        $this->model_key = "navigation_menu";
    }

    public function show(Request $request, string $slug): object
    {
        try
        {
            $website = CoreCache::getWebsite($request->header("hc-host"));
            $fetched = $this->query(function ($query) use ($slug, $website) {
                return $query->whereSlug($slug)
                    ->whereWebsiteId($website->id)
                    ->whereStatus(1)
                    ->firstorFail();
            });
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }
}

