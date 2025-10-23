<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\User\FcmTokenController;

/**
 * fcm token routes.
 */

Route::controller(FcmTokenController::class)->group(function () {
    Route::post('/fcm-token', 'update')->name('admin.fcm.token');
});
