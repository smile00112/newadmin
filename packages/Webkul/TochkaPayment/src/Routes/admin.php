<?php

use Illuminate\Support\Facades\Route;
use Webkul\TochkaPayment\Http\Controllers\Admin\PaymentHistoryController;
use Webkul\TochkaPayment\Http\Controllers\Admin\SettingsController;
use Webkul\TochkaPayment\Http\Controllers\Admin\TestOrderController;

Route::group(['prefix' => 'tochka-payment'], function () {
    Route::get('/test-order', [TestOrderController::class, 'index'])
        ->name('admin.tochka-payment.test-order.index');

    Route::post('/test-order', [TestOrderController::class, 'store'])
        ->name('admin.tochka-payment.test-order.store');

    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('admin.tochka-payment.settings.index');

    Route::put('/settings', [SettingsController::class, 'update'])
        ->name('admin.tochka-payment.settings.update');

    Route::get('/history', [PaymentHistoryController::class, 'index'])
        ->name('admin.tochka-payment.history.index');

    Route::get('/history/{id}', [PaymentHistoryController::class, 'show'])
        ->name('admin.tochka-payment.history.show');
});
