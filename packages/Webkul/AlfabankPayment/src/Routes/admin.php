<?php

use Illuminate\Support\Facades\Route;
use Webkul\AlfabankPayment\Http\Controllers\Admin\SettingsController;
use Webkul\AlfabankPayment\Http\Controllers\Admin\TestOrderController;

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

    Route::get('test-order', [TestOrderController::class, 'index'])
        ->defaults('_config', [
            'view' => 'alfabank-payment::admin.test-order.index',
        ])
        ->name('admin.alfabank.test-order.index');

    Route::post('test-order', [TestOrderController::class, 'send'])
        ->name('admin.alfabank.test-order.send');
});
