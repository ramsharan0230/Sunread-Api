<?php

namespace Modules\Page\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Page\Entities\PageScope;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Modules\Core\Entities\Website;

class PageScopeRepository extends BaseRepository
{
    public function __construct(PageScope $pageScope)
    {
        $this->model = $pageScope;
        $this->model_key = "page.scope";
        $this->rules = [];
    }

    public function updateOrCreate(array $stores, object $parent):void
    {
        if ( !is_array($stores) || count($stores) == 0 ) return;

        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.sync.before");
        try
        {
            $page_scopes = [];
            foreach($stores as $k => $store)
            {  
                if ($parent->website_id) {
                    $website_stores = Website::find($parent->website_id)->channels->map(function ($channel) {
                        return $channel->stores->pluck('id');
                    })->flatten(1)->toArray();
    
                    if (!in_array($store, $website_stores) && $store != 0) throw ValidationException::withMessages(["stores.$k" => "Store does not belong to this website"]);
                }

                $data = [
                    "page_id" => $parent->id,
                    "scope" => "store",
                    "scope_id" => $store
                ];

                if ($exist = $this->model->where($data)->first()) {
                    $page_scopes[] = $exist;
                    continue;
                }
                $page_scopes[] = $this->create($data);
            }

            $parent->page_scopes()->whereNotIn('id', array_filter(Arr::pluck($page_scopes, 'id')))->delete();
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        Event::dispatch("{$this->model_key}.sync.after", $page_scopes);
        DB::commit();
    }
}
