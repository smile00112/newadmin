<?php

use Illuminate\Support\Facades\Route;
use Webkul\TochkaPayment\Http\Controllers\RedirectController;

/**
 * Tochka Payment redirect routes (after bank redirect).
 */
Route::middleware(['web'])->prefix('payment/tochka')->group(function () {
    Route::match(['get', 'post'], 'success', [RedirectController::class, 'success'])->name('tochka-payment.redirect.success');
    Route::match(['get', 'post'], 'fail', [RedirectController::class, 'fail'])->name('tochka-payment.redirect.fail');
});
