<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Webkul\ExternalPayments\Http\Controllers\Api\CreatePaymentController;
use Webkul\ExternalPayments\Http\Middleware\ExternalSystemAuth;

$prefix = config('external-payments.api.prefix', 'external-payments');

Route::prefix($prefix)->middleware([ExternalSystemAuth::class])->group(function () {
    Route::post('/create', CreatePaymentController::class)->name('external-payments.create');
});
