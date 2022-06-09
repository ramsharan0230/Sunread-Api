<?php

namespace Modules\UrlRewrite\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Modules\UrlRewrite\Contracts\UrlRewriteInterface;
use Modules\UrlRewrite\Facades\UrlRewrite;
use Modules\UrlRewrite\Http\Controllers\RewriteBaseController;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\UrlRewrite\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {      
        $this->registerRepository();
        $this->registerRouteMacro();
        $this->registerFacade();  
        parent::boot();
    }

    protected function registerRouteMacro(): void
    {
        $queryParam = '.*';
        Route::macro('rewrites', function () use ($queryParam) {
            Route::get('{url?}', '\\'.RewriteBaseController::class)->where("url", $queryParam)->name('url.rewrite');
        });
    }

    protected function registerFacade(): void
    {
        $this->app->bind(UrlRewrite::class, function () {
            return $this->app->make(UrlRewriteInterface::class);
        });
    }

    protected function registerRepository(): void
    {
        $this->app->singleton(UrlRewriteInterface::class, function () {
            $urlRewriteConfig = $this->app['config']['url-rewrite'];
            $repositoryClass = $urlRewriteConfig['repository'];
            $modelClass = $urlRewriteConfig['model'];

            $repository = new $repositoryClass(new $modelClass);

            if (! $urlRewriteConfig['cache']) {
                return $repository;
            }

            $cacheClass = $urlRewriteConfig['cache-decorator'];

            return new $cacheClass($repository, $this->app['cache.store']);
        });
    }
    

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('UrlRewrite', '/Routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('UrlRewrite', '/Routes/api.php'));
    }
}
