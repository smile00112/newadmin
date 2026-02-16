<?php

use Illuminate\Support\Facades\Route;
use Webkul\TochkaPayment\Http\Controllers\Admin\PaymentHistoryController;
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
        Route::get('by-company/{companyId}', 'getByCompany')->name('admin.tochka-payment.settings.by-company');
        Route::post('', 'store')->name('admin.tochka-payment.settings.store');
        Route::post('webhook/subscribe', 'subscribeWebhook')->name('admin.tochka-payment.settings.webhook.subscribe');
        Route::post('webhook/unsubscribe', 'unsubscribeWebhook')->name('admin.tochka-payment.settings.webhook.unsubscribe');
        Route::get('webhook/status', 'getWebhookStatus')->name('admin.tochka-payment.settings.webhook.status');
    });

    /**
     * Payment history routes.
     */
    Route::controller(PaymentHistoryController::class)->prefix('payment-history')->group(function () {
        Route::get('', 'index')->name('admin.tochka-payment.payment-history.index');
        Route::get('{id}', 'show')->name('admin.tochka-payment.payment-history.show');
    });

    /**
     * Test order routes.
     */
    Route::controller(TestOrderController::class)->prefix('test-order')->group(function () {
        Route::get('', 'index')->name('admin.tochka-payment.test-order.index');
        Route::post('', 'store')->name('admin.tochka-payment.test-order.store');
    });
});
