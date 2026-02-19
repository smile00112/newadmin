<?php

namespace Webkul\TochkaPayment\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Core\Models\CoreConfig;

class TochkaPaymentServiceProvider extends ServiceProvider
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

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'tochka-payment');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'tochka-payment');

        $this->mapApiRoutes();

        $this->mapAdminRoutes();

        $this->mergeCoreConfigIntoConfig();
    }

    /**
     * Merge Tochka Payment settings from core_config into config.
     * Values from database override config file / .env when present.
     */
    protected function mergeCoreConfigIntoConfig(): void
    {
        try {
            $records = CoreConfig::where('code', 'like', 'tochka_payment.%')
                ->whereNull('channel_code')
                ->whereNull('locale_code')
                ->get();

            foreach ($records as $record) {
                $key = substr($record->code, strlen('tochka_payment.'));
                if ($key !== '') {
                    Config::set('tochka-payment.'.$key, $record->value);
                }
            }
        } catch (\Throwable $e) {
            // Ignore if core_config table or CoreConfig not available (e.g. during install)
        }
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/tochka-payment.php',
            'tochka-payment'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/menu.php',
            'menu.admin'
        );
    }

    /**
     * Define the "api" routes for the application.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware(['api'])
            ->group(__DIR__.'/../Routes/api.php');
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
}
