<?php

namespace Modules\Product\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Product\Entities\Feature;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductAttribute;
use Modules\Product\Entities\ProductAttributeBoolean;
use Modules\Product\Entities\ProductAttributeDecimal;
use Modules\Product\Entities\ProductAttributeInteger;
use Modules\Product\Entities\ProductAttributeString;
use Modules\Product\Entities\ProductAttributeText;
use Modules\Product\Entities\ProductAttributeTimestamp;
use Modules\Product\Observers\FeatureObserver;
use Modules\Product\Observers\ProductAttributeBooleanObserver;
use Modules\Product\Observers\ProductAttributeDecimalObserver;
use Modules\Product\Observers\ProductAttributeIntegerObserver;
use Modules\Product\Observers\ProductAttributeObserver;
use Modules\Product\Observers\ProductAttributeStringObserver;
use Modules\Product\Observers\ProductAttributeTextObserver;
use Modules\Product\Observers\ProductAttributeTimestampObserver;
use Modules\Product\Observers\ProductObserver;

class ProductServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Product';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'product';

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
        $this->registerObserver();
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
            module_path($this->moduleName, 'Config/product_image.php'), 'product_image'
        );
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/mapping.php'), 'mapping'
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

    public function registerObserver(): void
    {
        Product::observe(ProductObserver::class);
        ProductAttribute::observe(ProductAttributeObserver::class);
        ProductAttributeString::observe(ProductAttributeStringObserver::class);
        ProductAttributeBoolean::observe(ProductAttributeBooleanObserver::class);
        ProductAttributeDecimal::observe(ProductAttributeDecimalObserver::class);
        ProductAttributeInteger::observe(ProductAttributeIntegerObserver::class);
        ProductAttributeText::observe(ProductAttributeTextObserver::class);
        ProductAttributeTimestamp::observe(ProductAttributeTimestampObserver::class);
        Feature::observe(FeatureObserver::class);
    }
}
