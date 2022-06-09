<?php

namespace Modules\Erp\Console;

use Illuminate\Console\Command;
use Modules\Erp\Entities\ErpImport;
use Modules\Erp\Jobs\Mapper\ErpMigrateProductJob;

class ErpMigrate extends Command
{

    protected $signature = 'erp:migrate';

    protected $description = 'This command will migrate products form erp API.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): bool
    {
        // if ( ErpImport::whereStatus(1)->count() > 0 ) {
        //     $this->error("ERP Import is not complete.");
        //     return false;
        // }

        ErpMigrateProductJob::dispatch()->onQueue('erp');

        $this->info("ERP migration job started.");
        return true;
    }
}
