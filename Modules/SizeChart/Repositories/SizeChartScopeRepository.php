<?php

namespace Modules\SizeChart\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\SizeChart\Entities\SizeChartScope;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Modules\Core\Repositories\StoreFront\WebsiteRepository;

class SizeChartScopeRepository extends BaseRepository
{
    protected $websiteRepository;

    public function __construct(WebsiteRepository $websiteRepository)
    {
        $this->model = new SizeChartScope();
        $this->model_key = "sizecharts.scope";
        $this->rules = [];
        $this->websiteRepository = $websiteRepository;
    }

    public function updateOrCreate(array $stores, object $parent): void
    {
        DB::beginTransaction();
        Event::dispatch("{$this->model_key}.sync.before");
        try
        {
            $size_chart_scopes = [];
            if (is_array($stores) || count($stores) > 0)
            {
                foreach ($stores as $key => $store) {
                    $website_stores = $this->websiteRepository->fetch($parent->website_id, ["channels", "stores"])
                        ->stores->pluck('id')->flatten(1)->toArray();

                    if (!in_array($store, $website_stores) && $store != 0) {
                        throw ValidationException::withMessages(["stores.$key" => "Store does not belong to this website"]);
                    }
                    $data = [
                        "size_chart_id" => $parent->id,
                        "scope" => "store",
                        "scope_id" => $store,
                    ];

                    if ($exist = $this->model->where($data)->first()) {
                        $size_chart_scopes[] = $exist;
                        continue;
                    }
                    $size_chart_scopes[] = $this->create($data);
                }
            }

            $parent->size_chart_scopes()->whereNotIn('id', array_filter(Arr::pluck($size_chart_scopes, 'id')))->delete();
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        Event::dispatch("{$this->model_key}.sync.after", $size_chart_scopes);
        DB::commit();
    }
}
