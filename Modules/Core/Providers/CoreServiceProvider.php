<?php

namespace Modules\Core\Providers;

use Modules\Core\Entities\Locale;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Currency;
use Illuminate\Foundation\AliasLoader;
use Modules\Core\Entities\ActivityLog;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Entities\ExchangeRate;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Entities\Configuration;
use Modules\Core\Entities\JobTracker;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Core\Observers\LocaleObserver;
use Modules\Core\Observers\ChannelObserver;
use Modules\Core\Observers\CurrencyObserver;
use Modules\Core\Services\ActivityLogHelper;
use Modules\Core\Observers\ExchangeRateObserver;
use Modules\Core\Observers\WebsiteObserver;
use Modules\Core\Services\ConfigurationHelper;
use Modules\Core\Services\CoreCacheHelper;
use Modules\Core\Services\JobTrackerHelper;
use Modules\Core\Services\PriceFormatter;
use Modules\Core\Services\ResolverHelper;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Core';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'core';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->registerActivityLogger();
        $this->registerObserver();

        include __DIR__ . '/../Helpers/helpers.php';
        Validator::extend('decimal', 'Modules\Core\Contracts\Validations\Decimal@passes');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/configuration.php'), "configuration"
        );
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/website.php'), "website"
        );
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/currencies.php'), "currencies"
        );
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/time_zones.php'), "time_zones"
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

    /**
     * Register activity logger.
     *
     * @return void
     */
    public function registerActivityLogger()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('Audit', ActivityLoggerFacade::class);

        $this->app->singleton('audit', function () {
            return new ActivityLogHelper(new ActivityLog());
        });

        $this->app->singleton('siteConfig', function () {
            return new ConfigurationHelper(new Configuration());
        });

        $this->app->singleton('resolver', function () {
            return new ResolverHelper(new Website());
        });

        $this->app->singleton('coreCache', function () {
            return new CoreCacheHelper(new Website(), new Channel(), new Store());
        });

        $this->app->singleton('priceFormat', function () {
            return new PriceFormatter();
        });

        $this->app->singleton('jobtracker', function () {
            return new JobTrackerHelper(new JobTracker());
        });
    }

    /**
     * Register observers.
     *
     * @return void
     */
    private function registerObserver()
    {
        Channel::observe(ChannelObserver::class);
        Locale::observe(LocaleObserver::class);
        Currency::observe(CurrencyObserver::class);
        ExchangeRate::observe(ExchangeRateObserver::class);
        Website::observe(WebsiteObserver::class);
    }
}
