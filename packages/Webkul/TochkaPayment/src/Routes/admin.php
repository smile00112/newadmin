<?php

use Illuminate\Support\Facades\Route;
use Webkul\TochkaPayment\Http\Controllers\Admin\SettingsController;
use Webkul\TochkaPayment\Http\Controllers\Admin\TestOrderController;

/**
 * Tochka Payment admin routes.
 */
Route::group(['prefix' => 'tochka-payment'], function () {
    /**
     * Settings routes.
     */
    Route::controller(SettingsController::class)->prefix('settings')->group(function () {
        Route::get('', 'index')->name('admin.tochka-payment.settings.index');
        Route::post('', 'store')->name('admin.tochka-payment.settings.store');
    });

    /**
     * Test order routes.
     */
    Route::controller(TestOrderController::class)->prefix('test-order')->group(function () {
        Route::get('', 'index')->name('admin.tochka-payment.test-order.index');
        Route::post('', 'store')->name('admin.tochka-payment.test-order.store');
    });
});
