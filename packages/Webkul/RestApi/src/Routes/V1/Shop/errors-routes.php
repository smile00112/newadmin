<?php

use Illuminate\Support\Facades\Route;
use Webkul\RestApi\Http\Controllers\V1\Shop\ErrorReportController;

/**
 * Application errors routes (public, no auth).
 */
Route::controller(ErrorReportController::class)->prefix('errors')->group(function () {
    Route::post('', 'store');
});
