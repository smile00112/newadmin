<?php

namespace Webkul\Reporting\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;
use Webkul\Core\Http\Middleware\PreventRequestsDuringMaintenance;
use Webkul\Reporting\Listeners\AnalyticsOrderListener;

class ReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/Config/menu.php', 'menu.admin');
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'reporting');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'reporting');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->registerRoutes();
        $this->registerEventListeners();
        $this->registerCommands();
        $this->registerSchedule();
    }

    protected function registerRoutes(): void
    {
        Route::middleware(['web', PreventRequestsDuringMaintenance::class])->group(function () {
            Route::group([
                'middleware' => ['admin', NoCacheMiddleware::class],
                'prefix'     => config('app.admin_url'),
            ], function () {
                require __DIR__ . '/../Routes/admin-routes.php';
            });
        });

        Route::middleware('web')->group(function () {
            require __DIR__ . '/../Routes/api-routes.php';
        });
    }

    protected function registerEventListeners(): void
    {
        Event::listen('checkout.order.save.after', [AnalyticsOrderListener::class, 'afterCreated']);
        Event::listen('sales.order.update-status.after', [AnalyticsOrderListener::class, 'afterStatusUpdated']);
        Event::listen('sales.order.cancel.after', [AnalyticsOrderListener::class, 'afterCanceled']);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Webkul\Reporting\Console\Commands\AggregateDailyKpi::class,
            ]);
        }
    }

    protected function registerSchedule(): void
    {
        $this->callAfterResolving(\Illuminate\Console\Scheduling\Schedule::class, function (Schedule $schedule) {
            $schedule->command('analytics:aggregate-daily')->dailyAt('02:00');
        });
    }
}
