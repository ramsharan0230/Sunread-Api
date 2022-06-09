<?php

namespace Modules\Erp\Console;

use Illuminate\Console\Command;
use Modules\Erp\Jobs\Mapper\ErpProductImageUpdate;

class ErpImageMigrator extends Command
{
    protected $signature = 'erp:image-migrate';

    protected $description = 'This command will migrate image from source destination.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): bool
    {
        ErpProductImageUpdate::dispatch()->onQueue("erp");
        $this->info("Image migration started.");
        return true;
    }
}
