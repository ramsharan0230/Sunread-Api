<?php

namespace Modules\Erp\Console;

use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Modules\Erp\Jobs\BaseProductImport;
use Modules\Erp\Jobs\ProductImages;

class ErpImport extends Command
{
    public $signature = 'erp:import';

    protected $description = 'This command will import all data from Erp API.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): bool
    {
        // Import from API
        // ProductImages::dispatch()->onQueue("erp");
        Bus::batch(new BaseProductImport())->then(function (Batch $batch) {
            $batch->add([
                new ProductImages(),
            ]);
        })->allowFailures()->onQueue("erp")->dispatch();
        $this->info("All jobs dispatched.");
        return true;
    }
}
