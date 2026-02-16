<?php

namespace Webkul\TochkaPayment\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\TochkaPayment\Events\PaymentFailed;
use Webkul\TochkaPayment\Events\PaymentSuccess;
use Webkul\TochkaPayment\Listeners\SendTelegramNotificationOnPaymentFailed;
use Webkul\TochkaPayment\Listeners\SendTelegramNotificationOnPaymentSuccess;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Webkul\TochkaPayment\Models\TochkaPaymentSettings::class,
        \Webkul\TochkaPayment\Models\TochkaPaymentHistory::class,
        \Webkul\TochkaPayment\Models\TochkaPaymentWebhook::class,
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'tochka-payment');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'tochka-payment');

        $this->registerEventListeners();

        $this->mapAdminRoutes();
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Register event listeners.
     *
     * @return void
     */
    protected function registerEventListeners(): void
    {
        Event::listen(
            PaymentSuccess::class,
            SendTelegramNotificationOnPaymentSuccess::class
        );

        Event::listen(
            PaymentFailed::class,
            SendTelegramNotificationOnPaymentFailed::class
        );
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/tochka-payment.php',
            'tochka-payment'
        );
    }

    /**
     * Define the "admin" routes for the application.
     *
     * @return void
     */
    protected function mapAdminRoutes(): void
    {
        Route::middleware(['web', 'admin'])
            ->prefix(config('app.admin_url'))
            ->group(__DIR__.'/../Routes/admin.php');
    }

    /**
     * Define the "api" routes for the application.
     *
     * @return void
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware(['api'])
            ->prefix('api')
            ->group(__DIR__.'/../Routes/api.php');
    }

    /**
     * Define the "web" routes for payment redirects (success/fail).
     *
     * @return void
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware(['web'])
            ->group(__DIR__.'/../Routes/web.php');
    }
}
