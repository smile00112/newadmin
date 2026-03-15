<?php

namespace Webkul\RestApi\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Core\Exceptions\Handler as BaseHandler;
use Webkul\RestApi\Exceptions\Handler;
use Webkul\RestApi\Console\Commands\WarmCatalogV2CacheCommand;
use Webkul\RestApi\Console\Commands\WarmNomenclatureCacheCommand;
use Webkul\RestApi\Listeners\InvalidateCustomerBonusesCache;
use Webkul\RestApi\Listeners\InvalidateCustomerOrdersCache;
use Webkul\Sales\Models\Order;

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

        $this->registerSchedule();

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'rest-api');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'rest-api');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->bind(BaseHandler::class, Handler::class);

        $this->registerOrderCacheInvalidation();
        $this->registerBonusCacheInvalidation();

        // Load helpers
        require_once __DIR__.'/../Http/helpers.php';
    }

    /**
     * Register event listeners for customer orders cache invalidation.
     */
    protected function registerOrderCacheInvalidation(): void
    {
        $listener = InvalidateCustomerOrdersCache::class;

        Event::listen('checkout.order.save.after', [$listener, 'onOrderCreated']);
        Event::listen('sales.order.update-status.after', [$listener, 'onOrderStatusUpdated']);
        Event::listen('sales.order.cancel.after', [$listener, 'onOrderCanceled']);
        Event::listen('eloquent.deleted: '.Order::class, [$listener, 'onOrderDeleted']);
    }

    /**
     * Register event listeners for customer bonuses cache invalidation and warming.
     */
    protected function registerBonusCacheInvalidation(): void
    {
        $listener = InvalidateCustomerBonusesCache::class;
        Event::listen('bonus.balance.changed', [$listener, 'onBalanceChanged']);
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
        if ($this->app->runningInConsole()) {
            $this->commands([
                WarmNomenclatureCacheCommand::class,
                WarmCatalogV2CacheCommand::class,
            ]);
        }
    }

    /**
     * Register scheduled tasks (nomenclature and catalog-v2 cache warming).
     */
    protected function registerSchedule(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('nomenclature:warm-cache')->everyFiveMinutes();
            $schedule->command('catalog-v2:warm-cache')->everyFiveMinutes();
        });
    }
}
