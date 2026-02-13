<?php

use Illuminate\Support\Facades\Route;
use Webkul\IikoIntegration\Http\Controllers\Admin\IikoManagementController;
use Webkul\IikoIntegration\Http\Controllers\Admin\IikoSettingsController;
use Webkul\IikoIntegration\Http\Controllers\Admin\IikoSyncController;

/**
 * iiko Integration Admin routes.
 */
Route::prefix('iiko')->group(function () {
    Route::controller(IikoManagementController::class)->group(function () {
        Route::get('management', 'index')->name('admin.iiko.management.index');
        Route::post('management/organizations', 'getOrganizations')->name('admin.iiko.management.organizations');
        Route::post('management/terminals', 'getTerminals')->name('admin.iiko.management.terminals');
        Route::post('management/menu', 'getMenu')->name('admin.iiko.management.menu');
        Route::post('management/nomenclature', 'getNomenclature')->name('admin.iiko.management.nomenclature');
        Route::post('management/import-nomenclature', 'importNomenclature')->name('admin.iiko.management.import-nomenclature');
        Route::post('management/customer-by-phone', 'getCustomerByPhone')->name('admin.iiko.management.customer-by-phone');
        Route::post('management/promotions', 'getPromotions')->name('admin.iiko.management.promotions');
        Route::post('management/payment-types', 'getPaymentTypes')->name('admin.iiko.management.payment-types');
        Route::post('management/import-terminal', 'importTerminal')->name('admin.iiko.management.import-terminal');
    });

    Route::controller(IikoSettingsController::class)->group(function () {
        Route::get('settings', 'index')->name('admin.iiko.settings.index');
        Route::post('settings', 'store')->name('admin.iiko.settings.store');
        Route::post('settings/test-connection', 'testConnection')->name('admin.iiko.settings.test');
    });

    Route::controller(IikoSyncController::class)->group(function () {
        Route::get('sync', 'index')->name('admin.iiko.sync');
        Route::post('sync/organizations', 'syncOrganizations')->name('admin.iiko.sync.organizations');
        Route::post('sync/menu', 'syncMenu')->name('admin.iiko.sync.menu');
        Route::post('sync/order/{orderId}', 'syncOrder')->name('admin.iiko.sync.order');
        Route::get('sync/logs', 'viewSyncLog')->name('admin.iiko.sync.logs');
    });
});
