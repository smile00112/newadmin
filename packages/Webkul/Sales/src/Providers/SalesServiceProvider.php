<?php

namespace Webkul\Sales\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Observers\OrderObserver;

class SalesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Order::observe(OrderObserver::class);
    }
}
