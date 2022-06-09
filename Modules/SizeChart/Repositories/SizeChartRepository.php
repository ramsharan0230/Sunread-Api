<?php

namespace Modules\SizeChart\Repositories;

use Exception;
use Modules\Core\Entities\Store;
use Modules\Core\Repositories\BaseRepository;
use Modules\SizeChart\Entities\SizeChart;
use Illuminate\Validation\ValidationException;
use Modules\SizeChart\Entities\SizeChartContent;
use Modules\SizeChart\Entities\SizeChartScope;
use Illuminate\Support\Str;

class SizeChartRepository extends BaseRepository
{
    protected $store_model;
    protected $pageScope;

    public function __construct()
    {
        $this->model = new SizeChart();
        $this->model_key = "sizeChart";
        $this->rules = [
            "title" => "required",
            "content" => "sometimes|string",
            "status" => "required|boolean",
            "website_id" => "required|exists:websites,id",
            "stores" => "required|array",
            "attributes" => "required|array",
        ];
        $this->store_model = new Store();
        $this->sizeChartScope = new SizeChartScope();
        $this->sizeChartContent = new SizeChartContent();
    }

    public function validateSlug(array $data, ?int $id = null): void
    {
        try {
            $model = ($id) ? $this->model->where('id', '!=', $id) : $this->model;

            $exist_scope_slug = $model->whereSlug($data["slug"])
                ->whereHas("size_chart_scopes", function ($query) {
                $query->whereScope("store")->whereScopeId(0);
            })->first();

            if ($exist_scope_slug) {
                throw ValidationException::withMessages(["slug" => "Slug has already taken."]);
            };

            $exist_content_slug = "";
            foreach ($data["attributes"] as $key=>$attribute) {
                $exist_content_slug = $this->sizeChartContent->whereSlug($attribute["slug"])->first();
            }
            if ($exist_content_slug) {
                throw ValidationException::withMessages(["slug" => "Slug has already taken for Content."]);
            }

        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function show(int $id): array
    {
        try
        {
            $data = $this->fetch($id, [ "size_chart_scopes", "website", "size_chart_contents" ]);
            $stores = $data->size_chart_scopes->pluck("scope_id")->toArray();
            $item = $data->toArray();
            unset($item["size_chart_scopes"]);
            $item["stores"] = $stores;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return $item;
    }
}
