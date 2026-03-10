<?php

namespace App\Providers;

use App\Listeners\Order\AnalyticsOrderListener;
use App\Repositories\ApplicationErrorRepository;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ApplicationErrorRepository::class);

        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });

        // Configure API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Analytics event listeners
        Event::listen('checkout.order.save.after', [AnalyticsOrderListener::class, 'afterCreated']);
        Event::listen('sales.order.update-status.after', [AnalyticsOrderListener::class, 'afterStatusUpdated']);
        Event::listen('sales.order.cancel.after', [AnalyticsOrderListener::class, 'afterCanceled']);
    }
}
