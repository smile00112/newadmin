<?php

use Illuminate\Support\Facades\Route;
use Webkul\AlfabankPayment\Http\Controllers\PaymentController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('alfabank')->group(function () {
        // Payment callback from bank
        Route::any('payment/callback', [PaymentController::class, 'callback'])
            ->name('alfabank.payment.callback');

        // User return from payment page
        Route::get('payment/return', [PaymentController::class, 'return'])
            ->name('alfabank.payment.return');

        // Saved cards API
        Route::middleware(['customer'])->group(function () {
            Route::get('saved-cards', [PaymentController::class, 'getSavedCards'])
                ->name('alfabank.saved-cards.get');

            Route::post('saved-cards/select', [PaymentController::class, 'setSelectedCard'])
                ->name('alfabank.saved-cards.select');
        });
    });
});
