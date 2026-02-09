<?php

use Illuminate\Support\Facades\Route;
use Webkul\AlfabankPayment\Http\Controllers\Admin\SettingsController;

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::prefix('alfabank')->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])
            ->defaults('_config', [
                'view' => 'alfabank-payment::admin.settings.index',
            ])
            ->name('admin.alfabank.settings.index');

        Route::put('settings', [SettingsController::class, 'update'])
            ->defaults('_config', [
                'redirect' => 'admin.alfabank.settings.index',
            ])
            ->name('admin.alfabank.settings.update');
    });
});
