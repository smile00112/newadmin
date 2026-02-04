<?php

namespace Webkul\Notification\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen('checkout.order.save.after', 'Webkul\Notification\Listeners\Order@createOrder');

        Event::listen('sales.order.update-status.after', 'Webkul\Notification\Listeners\Order@updateOrder');

        // Product notifications
        Event::listen('catalog.product.update.before', 'Webkul\Notification\Listeners\Product@beforeUpdate');

        Event::listen('catalog.product.update.after', 'Webkul\Notification\Listeners\Product@afterUpdate');
    }
}
