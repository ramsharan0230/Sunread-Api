<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\Core\Console\PartialMigrate;
use Modules\Core\Console\RedisClear;
use Modules\Erp\Console\ErpAttributeOptionMigrate;
use Modules\Erp\Console\ErpImageMigrator;
use Modules\Erp\Console\ErpImport;
use Modules\Erp\Console\ErpMigrate;
use Modules\Product\Console\ElasticSearchImport;
use Modules\GeoIp\Console\GeoIpDbUpdator;
use Modules\Product\Console\DeleteIndices;
use Modules\Product\Console\ProductUrlGenerator;
use Modules\Product\Console\ElasticSearchReindex;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ErpImport::class,
        ErpMigrate::class,
        // CategoryMigrate::class,
        PartialMigrate::class,
        ElasticSearchImport::class,
        ErpAttributeOptionMigrate::class,
        RedisClear::class,
        GeoIpDbUpdator::class,
        ProductUrlGenerator::class,
        DeleteIndices::class,
        ElasticSearchReindex::class,
        ErpImageMigrator::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('telescope:prune')->hourly();
        $schedule->command('geoip:update')->weekly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
