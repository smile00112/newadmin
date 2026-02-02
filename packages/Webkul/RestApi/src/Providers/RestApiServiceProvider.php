<?php

namespace Webkul\RestApi\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Core\Exceptions\Handler as BaseHandler;
use Webkul\RestApi\Exceptions\Handler;

class RestApiServiceProvider extends ServiceProvider
{
    /**
     * Register your middleware aliases here.
     *
     * @var array
     */
    protected $middlewareAliases = [
        'etag'             => \Webkul\RestApi\Http\Middleware\ETagMiddleware::class,
        'sanctum.admin'    => \Webkul\RestApi\Http\Middleware\AdminMiddleware::class,
        'sanctum.customer' => \Webkul\RestApi\Http\Middleware\CustomerMiddleware::class,
        'sanctum.locale'   => \Webkul\RestApi\Http\Middleware\LocaleMiddleware::class,
        'sanctum.currency' => \Webkul\RestApi\Http\Middleware\CurrencyMiddleware::class,
        'api.response-time' => \Webkul\RestApi\Http\Middleware\ResponseTimeMiddleware::class,
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->activateMiddlewareAliases();

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'rest-api');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'rest-api');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->bind(BaseHandler::class, Handler::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mapApiRoutes();

        $this->registerCommands();
    }

    /**
     * Activate middleware aliases.
     *
     * @return void
     */
    protected function activateMiddlewareAliases()
    {
        collect($this->middlewareAliases)->each(function ($className, $alias) {
            $this->app['router']->aliasMiddleware($alias, $className);
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware(['api', 'etag', 'api.response-time'])
            ->group(__DIR__.'/../Routes/api.php');
    }

    /**
     * Register the console commands of this package.
     */
    protected function registerCommands(): void
    {
//        if ($this->app->runningInConsole()) {
//            $this->commands([
//
//            ]);
//        }
    }
}
