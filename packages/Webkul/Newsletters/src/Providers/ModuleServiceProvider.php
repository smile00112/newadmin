<?php

namespace Webkul\Newsletters\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Models\Company;
use Webkul\Newsletters\Observers\CustomerNumberObserver;
use Webkul\Newsletters\Observers\CompanyObserver;
use Webkul\Newsletters\Console\Commands\TestWebSocketBroadcast;
use Webkul\Newsletters\Console\Commands\ResetBlockedInstances;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'newsletters');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'newsletters');

        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        $this->publishes([
            __DIR__.'/../Resources/lang' => resource_path('lang/vendor/newsletters'),
        ], 'lang');

        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/newsletters'),
        ], 'views');

        // Register model observers
        CustomerNumber::observe(CustomerNumberObserver::class);
        Company::observe(CompanyObserver::class);

        // Register scheduled tasks
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('newsletters:reset-blocked-instances')->dailyAt('00:00');
        });

        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware(
            'newsletters.permission',
            \Webkul\Newsletters\Http\Middleware\CheckNewsletterPermission::class
        );
        $router->aliasMiddleware(
            'newsletters.company',
            \Webkul\Newsletters\Http\Middleware\EnsureCompanyAccess::class
        );
        $router->aliasMiddleware(
            'newsletters.account.balance',
            \Webkul\Newsletters\Http\Middleware\CheckAccountBalance::class
        );
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerCommands();
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/newsletters.php', 'newsletters'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/reverb.php', 'reverb'
        );
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TestWebSocketBroadcast::class,
                ResetBlockedInstances::class,
            ]);
        }
    }
}
