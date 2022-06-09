<?php

namespace Modules\Erp\Console;

use Illuminate\Console\Command;
use Modules\Erp\Jobs\Mapper\ErpMigrateAttributeOptionJob;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ErpAttributeOptionMigrate extends Command
{
    protected $signature = 'erp:attribute-options-migrate';

    protected $description = 'Import all color attribute option from erp.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): bool
    {
        ErpMigrateAttributeOptionJob::dispatch()->onQueue('erp');
        $this->info("All jobs dispatched.");
        return true;
    }

}
