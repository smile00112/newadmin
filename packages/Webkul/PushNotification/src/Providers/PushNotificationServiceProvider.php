<?php

namespace Webkul\PushNotification\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\PushNotification\Listeners\LiveActivityStatusChanged;
use Webkul\PushNotification\Listeners\OrderStatusChanged;

class PushNotificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'push_notification');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');

        $this->registerEvents();
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();

        $this->app->bind(
            \Webkul\PushNotification\Contracts\CustomerPushToken::class,
            \Webkul\PushNotification\Models\CustomerPushToken::class
        );

        $this->app->bind(
            \Webkul\PushNotification\Contracts\OrderLiveActivityToken::class,
            \Webkul\PushNotification\Models\OrderLiveActivityToken::class
        );

        $this->app->bind(
            \Webkul\PushNotification\Contracts\PushCampaign::class,
            \Webkul\PushNotification\Models\PushCampaign::class
        );

        $this->app->bind(
            \Webkul\PushNotification\Contracts\PushCampaignLog::class,
            \Webkul\PushNotification\Models\PushCampaignLog::class
        );

        $this->app->singleton(
            \Webkul\PushNotification\Services\PushCampaignService::class
        );
    }

    /**
     * Register event listeners.
     */
    protected function registerEvents(): void
    {
        Event::listen('sales.order.update-status.after', [OrderStatusChanged::class, 'handle']);
        Event::listen('sales.order.update-status.after', [LiveActivityStatusChanged::class, 'handle']);
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php',
            'core'
        );
    }
}
