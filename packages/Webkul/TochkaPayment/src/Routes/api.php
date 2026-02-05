<?php

use Illuminate\Support\Facades\Route;
use Webkul\TochkaPayment\Http\Controllers\Api\PaymentController;
use Webkul\TochkaPayment\Http\Middleware\ApiTokenAuth;

Route::group(['prefix' => 'tochka-payment'], function () {
    // Create payment endpoint (requires authentication)
    Route::post('/create', [PaymentController::class, 'create'])
        ->middleware([ApiTokenAuth::class])
        ->name('tochka-payment.create');

    // Callback endpoint (public, no authentication)
    Route::post('/callback', [PaymentController::class, 'callback'])
        ->name('tochka-payment.callback');
});
