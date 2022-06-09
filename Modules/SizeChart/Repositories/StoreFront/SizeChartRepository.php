<?php

namespace Modules\SizeChart\Repositories\StoreFront;

use Exception;
use Modules\Core\Repositories\BaseRepository;
use Modules\SizeChart\Entities\SizeChart;

class SizeChartRepository extends BaseRepository
{
    public $config_fields, $parent = [];

    public function __construct()
    {
        $this->model = new SizeChart();
        $this->model_key = "sizechart";
        $this->without_pagination = true;
    }

    public function sizeChart(object $request, string $slug): array
    {
        try
        {
            $coreCache = $this->getCoreCache($request);
            $sizeChart = $this->model->whereWebsiteId($coreCache->website->id)
                ->whereSlug($slug)
                ->whereStatus(1)
                ->with(["size_chart_contents", "size_chart_scopes" => function ($query) { $query->where("scope_id", 0); }])
                ->firstOrFail();

            $fetched = $sizeChart->toArray();
        }
        catch( Exception $exception )
        {
            throw $exception;
        }
        return $fetched;
    }
}
