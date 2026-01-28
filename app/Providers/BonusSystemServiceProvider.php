<?php

namespace App\Providers;

use App\Payment\BonusPayment;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use App\Listeners\Cart\BonusCartListener;
use App\Listeners\Order\BonusOrderListener;

class BonusSystemServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register payment method
        Config::set('payment_methods.bonus', [
            'code'        => 'bonus',
            'title'       => 'Бонусами',
            'description' => 'Оплата бонусами',
            'class'       => BonusPayment::class,
            'active'      => true,
            'sort'        => 3,
        ]);

        // Merge bonus system config into core config
        $bonusConfig = require app_path('Config/bonus_system.php');
        $coreConfig = Config::get('core', []);
        $coreConfig = array_merge($coreConfig, $bonusConfig);
        Config::set('core', $coreConfig);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen('checkout.cart.collect.totals.after', \App\Listeners\Cart\BonusCartListener::class . '@handle');
        Event::listen('checkout.order.save.after', \App\Listeners\Order\BonusOrderListener::class . '@handleOrderCreated');
        Event::listen('sales.order.update-status.after', \App\Listeners\Order\BonusOrderListener::class . '@handleOrderStatusUpdated');

        // Extend Customer model with BonusTrait
        $this->extendCustomerModel();

        // Register admin routes
        $this->mapAdminRoutes();
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
            ->group(function () {
                Route::controller(\App\Http\Controllers\Admin\BonusLevelController::class)
                    ->prefix('bonus-levels')
                    ->group(function () {
                        Route::get('', 'index')->name('admin.bonus-levels.index');
                        Route::get('create', 'create')->name('admin.bonus-levels.create');
                        Route::post('', 'store')->name('admin.bonus-levels.store');
                        Route::get('{id}/edit', 'edit')->name('admin.bonus-levels.edit');
                        Route::put('{id}', 'update')->name('admin.bonus-levels.update');
                        Route::delete('{id}', 'destroy')->name('admin.bonus-levels.destroy');
                    });

                Route::controller(\App\Http\Controllers\Admin\BonusHistoryController::class)
                    ->prefix('bonus-history')
                    ->group(function () {
                        Route::get('', 'index')->name('admin.bonus-history.index');
                    });
            });
    }

    /**
     * Extend Customer model with BonusTrait.
     *
     * @return void
     */
    protected function extendCustomerModel(): void
    {
        \Webkul\Customer\Models\Customer::resolveRelationUsing('bonusLevel', function ($customerModel) {
            return $customerModel->belongsTo(\App\Models\BonusLevel::class, 'bonus_level_id');
        });

        \Webkul\Customer\Models\Customer::resolveRelationUsing('bonusHistory', function ($customerModel) {
            return $customerModel->hasMany(\App\Models\BonusHistory::class, 'customer_id');
        });

        // Add methods to Customer model
        \Webkul\Customer\Models\Customer::macro('getAvailableBonusBalance', function () {
            return app(\App\Services\BonusService::class)->getAvailableBonusBalance($this);
        });

        \Webkul\Customer\Models\Customer::macro('getTotalBonusBalance', function () {
            return (float) ($this->bonus_balance ?? 0);
        });

        // Override getFillable() to include bonus fields
        $originalFillable = (new \Webkul\Customer\Models\Customer())->getFillable();
        $bonusFields = ['bonus_balance', 'bonus_level_id', 'bonus_total_spent', 'bonus_total_orders'];
        $mergedFillable = array_unique(array_merge($originalFillable, $bonusFields));

        \Webkul\Customer\Models\Customer::macro('getFillable', function () use ($mergedFillable) {
            return $mergedFillable;
        });
    }
}
