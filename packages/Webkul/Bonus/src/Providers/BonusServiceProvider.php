<?php

namespace Webkul\Bonus\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Webkul\Bonus\Listeners\Order as BonusOrderListener;

class BonusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'bonus');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'bonus');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');

        $this->registerEvents();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Register events.
     *
     * @return void
     */
    protected function registerEvents()
    {
        Event::listen('checkout.order.save.after', [BonusOrderListener::class, 'afterCreated']);
        Event::listen('sales.order.update-status.after', [BonusOrderListener::class, 'afterStatusUpdated']);
        Event::listen('sales.order.cancel.after', [BonusOrderListener::class, 'afterCanceled']);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php',
            'core'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/acl.php',
            'acl'
        );
    }
}
