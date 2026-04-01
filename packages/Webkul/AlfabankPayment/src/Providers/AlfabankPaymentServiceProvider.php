<?php

namespace Webkul\AlfabankPayment\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\AlfabankPayment\Listeners\RefundOrderListener;

class AlfabankPaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'alfabank-payment');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'alfabank-payment');

        $this->mapWebRoutes();

        $this->mapAdminRoutes();

        Event::listen('sales.refund.save.before', RefundOrderListener::class);

        $this->publishAssets();
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/alfabank-payment.php',
            'alfabank-payment'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/menu.php',
            'menu.admin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/acl.php',
            'acl'
        );
    }

    /**
     * Define the "web" routes for the application.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware(['web'])
            ->group(__DIR__.'/../Routes/web.php');
    }

    /**
     * Define the "admin" routes for the application.
     */
    protected function mapAdminRoutes(): void
    {
        Route::middleware(['web', 'admin'])
            ->prefix(config('app.admin_url'))
            ->group(__DIR__.'/../Routes/admin.php');
    }

    /**
     * Publish assets.
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../Resources/assets' => public_path('vendor/alfabank-payment'),
        ], 'alfabank-payment-assets');
    }
}
