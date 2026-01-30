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
        $this->registerContracts();
    }

    /**
     * Register contract bindings.
     *
     * @return void
     */
    protected function registerContracts()
    {
        $this->app->bind(
            \Webkul\Bonus\Contracts\CustomerBonus::class,
            \Webkul\Bonus\Models\CustomerBonus::class
        );

        $this->app->bind(
            \Webkul\Bonus\Contracts\BonusLevel::class,
            \Webkul\Bonus\Models\BonusLevel::class
        );

        $this->app->bind(
            \Webkul\Bonus\Contracts\BonusTransaction::class,
            \Webkul\Bonus\Models\BonusTransaction::class
        );

        $this->app->bind(
            \Webkul\Bonus\Contracts\BonusSetting::class,
            \Webkul\Bonus\Models\BonusSetting::class
        );
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
        
        // Register listener for cart bonus calculation
        Event::listen('checkout.cart.collect.totals.after', \App\Listeners\Cart\BonusCartListener::class);
        
        // Register listener for auto-apply bonus recalculation
        Event::listen('checkout.cart.add.after', \App\Listeners\Cart\AutoApplyBonusListener::class);
        Event::listen('checkout.cart.update.after', \App\Listeners\Cart\AutoApplyBonusListener::class);
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

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/menu.php',
            'menu.admin'
        );
    }
}
