<?php

namespace Modules\GeoIp\Console;

use Illuminate\Console\Command;
use Modules\GeoIp\Facades\GeoIp;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GeoIpDbUpdator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geoip:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the geoip database.';

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
        $GeoIpUpdate = GeoIp::update();
        $this->info($GeoIpUpdate);
    }
}
