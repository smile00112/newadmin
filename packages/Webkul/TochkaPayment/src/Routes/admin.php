<?php

use Illuminate\Support\Facades\Route;
use Webkul\TochkaPayment\Http\Controllers\Admin\PaymentHistoryController;

Route::group(['prefix' => 'tochka-payment'], function () {
    Route::get('/history', [PaymentHistoryController::class, 'index'])
        ->name('admin.tochka-payment.history.index');

    Route::get('/history/{id}', [PaymentHistoryController::class, 'show'])
        ->name('admin.tochka-payment.history.show');
});
