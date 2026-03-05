<?php

use Illuminate\Support\Facades\Route;
use Webkul\PushNotification\Http\Controllers\Api\PushTokenController;

Route::group([
    'middleware' => ['auth:sanctum', 'sanctum.customer'],
    'prefix'    => 'api/v1',
], function () {
    Route::controller(PushTokenController::class)->prefix('customer/push-token')->group(function () {
        Route::post('', 'store');
        Route::delete('', 'destroy');
    });
});
