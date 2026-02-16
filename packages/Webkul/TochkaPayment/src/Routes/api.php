<?php

use Illuminate\Support\Facades\Route;
use Webkul\TochkaPayment\Http\Controllers\Api\PaymentController;
use Webkul\TochkaPayment\Http\Controllers\Api\WebhookController;

/**
 * Tochka Payment API routes.
 */
Route::group(['prefix' => 'tochka-payment'], function () {
    /**
     * Payment routes.
     */
    Route::controller(PaymentController::class)->prefix('payments')->group(function () {
        Route::post('', 'create')->name('api.tochka-payment.payments.create');
        Route::post('callback', 'callback')->name('tochka-payment.callback');
        Route::get('{id}/status', 'checkStatus')->name('api.tochka-payment.payments.check-status');
        Route::post('check-status', 'checkStatus')->name('api.tochka-payment.payments.check-status-post');
    });

    /**
     * Webhook routes (no authentication required, verified by JWT signature).
     */
    Route::controller(WebhookController::class)->prefix('webhook')->group(function () {
        Route::post('', 'handle')->name('api.tochka-payment.webhook.handle');
    });
});
