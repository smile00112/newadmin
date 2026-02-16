<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Webkul\ExternalPayments\Http\Controllers\Web\WooCommerceCallbackController;

Route::prefix('external-payments/woocommerce')->group(function () {
    Route::get('success', [WooCommerceCallbackController::class, 'success'])
        ->name('external-payments.woocommerce.success');

    Route::get('failure', [WooCommerceCallbackController::class, 'failure'])
        ->name('external-payments.woocommerce.failure');
});
