<?php

namespace Modules\Product\Console;

use Illuminate\Console\Command;
use Modules\Product\Jobs\ProductUrlGeneratorJob;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ProductUrlGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:urlkey-generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Product url key regenerate.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        ProductUrlGeneratorJob::dispatch()->onQueue("high");
        $this->info("Product url key regenerate job dispatched.");
    }

}
