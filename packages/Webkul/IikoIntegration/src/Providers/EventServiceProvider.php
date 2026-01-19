<?php

namespace Webkul\IikoIntegration\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Webkul\IikoIntegration\Listeners\OrderSyncListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'checkout.order.save.after' => [
            [OrderSyncListener::class, 'handleOrderCreated'],
        ],

        'sales.order.cancel.after' => [
            [OrderSyncListener::class, 'handleOrderCancelled'],
        ],

        'sales.order.update-status.after' => [
            [OrderSyncListener::class, 'handleOrderStatusUpdated'],
        ],
    ];
}
