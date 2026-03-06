<?php

namespace Webkul\PushNotification\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models to register.
     *
     * @var array
     */
    protected $models = [
        \Webkul\PushNotification\Models\CustomerPushToken::class,
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->register(PushNotificationServiceProvider::class);
    }
}
