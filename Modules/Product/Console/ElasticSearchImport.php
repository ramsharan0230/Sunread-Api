<?php

namespace Modules\Product\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Entities\JobTracker;
use Modules\Core\Entities\Store;
use Modules\Product\Entities\Product;
use Modules\Product\Jobs\ReindexerTest;
use Modules\Product\Jobs\ReindexMigrator;

class ElasticSearchImport extends Command
{
    protected $signature = 'reindexer:reindex';

    protected $description = 'Import all the data to the elasticsearch';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        // $exist_reindex_job = DB::table("jobs")->whereQueue("index")->first();
        // if(!$exist_reindex_job) {
        //     ReindexMigrator::dispatch()->onQueue("index");

        //     $exist_jobtracker = JobTracker::whereName("reindex")->whereStatus(0)->first();
        //     if($exist_jobtracker) $exist_jobtracker->delete();

        //     $product_count = Product::count();
        //     $store_count = Store::count();

        //     JobTracker::create([
        //         "name" => "reindex",
        //         "total_jobs" => ($product_count * $store_count) + 2,
        //         "completed_jobs" => 0,
        //         "failed_jobs" => 0,
        //         "status" => 0
        //     ]);
        //     $this->info("All data reindexed successfully");
        // }
        // else $this->info("Reindex running. Try later!");

        $cache_name = "reindex*";
        $cache_key = Redis::keys($cache_name);
        if( count($cache_key) > 0 ) Redis::del($cache_key);
        $this->info("Reindex cache cleared successfully");
        ReindexerTest::dispatch()->onQueue("index");
        $this->info("All data reindexed successfully");
    }
}
