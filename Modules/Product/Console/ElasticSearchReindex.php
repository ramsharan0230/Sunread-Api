<?php

namespace Modules\Product\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Core\Entities\JobTracker;
use Modules\Core\Entities\Store;
use Modules\Product\Entities\Product;
use Modules\Product\Jobs\ReindexerTest;

class ElasticSearchReindex extends Command
{
    protected $signature = 'reindexer:test';

    protected $description = 'Import all the data to the elasticsearch';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        ReindexerTest::dispatch()->onQueue("index");
    }
}
