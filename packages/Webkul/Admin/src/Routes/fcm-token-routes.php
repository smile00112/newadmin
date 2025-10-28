<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\User\FcmTokenController;
use Webkul\Admin\Http\Controllers\User\FcmNotificationController;

/**
 * fcm token routes.
 */

Route::controller(FcmTokenController::class)->group(function () {
    Route::post('/fcm-token', 'update')->name('admin.fcm.token');
});

/**
 * fcm notification routes.
 */
Route::controller(FcmNotificationController::class)->prefix('fcm')->group(function () {
    Route::get('/test-page', 'testPage')->name('admin.fcm.test-page');
    Route::post('/send-test', 'sendTest')->name('admin.fcm.send-test');
    Route::post('/send-all', 'sendToAll')->name('admin.fcm.send-all');
});
