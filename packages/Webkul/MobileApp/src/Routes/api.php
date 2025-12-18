<?php

use Illuminate\Support\Facades\Route;
use Webkul\MobileApp\Http\Controllers\Api\MobileSettingsController;
use Webkul\MobileApp\Http\Controllers\Admin\SettingsController;

/**
 * API routes for mobile app.
 */
Route::group([
    'prefix'     => 'api/v1',
    'middleware' => ['api', ],//'sanctum.locale', 'sanctum.currency'
], function () {
    Route::group(['middleware' => []], function () { //'auth:sanctum', 'sanctum.customer'
        Route::get('mobile-settings', [MobileSettingsController::class, 'index'])
            ->name('api.v1.mobile_app.settings');
    });
});

/**
 * Admin routes for mobile app settings.
 */
Route::group([
    'prefix'     => config('app.admin_url', 'admin'),
    'middleware' => ['web', 'admin'],
], function () {
    Route::prefix('mobile-app')->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])
            ->name('admin.mobile_app.settings.index');

        Route::post('settings', [SettingsController::class, 'store'])
            ->name('admin.mobile_app.settings.store');

        Route::get('settings/options/{source}', [SettingsController::class, 'getFieldOptions'])
            ->name('admin.mobile_app.settings.options');
    });
});

