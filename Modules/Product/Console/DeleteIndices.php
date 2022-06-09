<?php

namespace Modules\Product\Console;

use Illuminate\Console\Command;
use Modules\Product\Jobs\DeleteIndices as JobsDeleteIndices;

class DeleteIndices extends Command
{
    protected $signature = 'delete:indices';

    protected $description = 'Delete all the index of the elasticsearch';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        JobsDeleteIndices::dispatch()->onQueue("index");
        $this->info("All indices deleted successfully");
    }
}
