<?php

use Illuminate\Support\Facades\Route;
use Webkul\RestApi\Http\Controllers\V1\Admin\User\AccountController;
use Webkul\RestApi\Http\Controllers\V1\Admin\User\AuthController;
use Webkul\RestApi\Http\Controllers\V1\Admin\User\MultiChannelAuthController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');

    Route::post('forgot-password', 'forgotPassword');
});

/**
 * Multi-channel authentication routes for admin.
 */
Route::controller(MultiChannelAuthController::class)->prefix('auth')->group(function () {
    Route::post('sms/initiate', 'initiateSmsAuth');
    Route::post('whatsapp/initiate', 'initiateWhatsAppAuth');
    Route::post('telegram/initiate', 'initiateTelegramAuth');
    Route::post('verify', 'verifyAndAuthenticate');
    Route::post('reset-token', 'resetToken');
    Route::post('verify-reset', 'verifyResetAndGenerateToken');
});

Route::group(['middleware' => ['auth:sanctum', 'sanctum.admin']], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::delete('logout', 'logout');
    });

    Route::controller(AccountController::class)->group(function () {
        Route::get('get', 'get');

        Route::put('update', 'update');
    });
});
