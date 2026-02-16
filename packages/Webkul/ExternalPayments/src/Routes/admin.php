<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Webkul\ExternalPayments\Http\Controllers\Admin\ExternalSystemController;

Route::group(['prefix' => 'external-payments'], function () {
    Route::controller(ExternalSystemController::class)->prefix('systems')->name('admin.external-payments.systems.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('', 'store')->name('store');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::put('{id}', 'update')->name('update');
    });
});
