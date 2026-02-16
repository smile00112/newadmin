<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\ExternalPayments\Listeners\SendExternalPaymentWebhookListener;

class ExternalPaymentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'external-payments');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'external-payments');

        $this->mapApiRoutes();
        $this->mapAdminRoutes();
        $this->mapWebRoutes();

        Event::listen(
            'external_payments.payment.success',
            SendExternalPaymentWebhookListener::class.'@handlePaymentSuccess'
        );
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/external-payments.php',
            'external-payments'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/menu.php',
            'menu.admin'
        );
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware(['api'])
            ->group(__DIR__.'/../Routes/api.php');
    }

    protected function mapAdminRoutes(): void
    {
        Route::middleware(['web', 'admin'])
            ->prefix(config('app.admin_url'))
            ->group(__DIR__.'/../Routes/admin.php');
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware(['web'])
            ->group(__DIR__.'/../Routes/web.php');
    }
}
