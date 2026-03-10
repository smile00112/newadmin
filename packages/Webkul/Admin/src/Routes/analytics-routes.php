<?php

use App\Http\Controllers\Admin\Analytics\AnalyticsDashboardController;
use Illuminate\Support\Facades\Route;

/**
 * Analytics Dashboard routes.
 */
Route::prefix('analytics')->group(function () {
    Route::controller(AnalyticsDashboardController::class)->group(function () {
        // Executive Dashboard
        Route::get('', 'executive')->name('admin.analytics.executive.index');
        Route::get('executive/stats', 'executiveStats')->name('admin.analytics.executive.stats');

        // Daily Management Dashboard (K)
        Route::get('daily', 'daily')->name('admin.analytics.daily.index');
        Route::get('daily/stats', 'dailyStats')->name('admin.analytics.daily.stats');

        // Product Analytics Dashboard
        Route::get('product', 'product')->name('admin.analytics.product.index');
        Route::get('product/stats', 'productStats')->name('admin.analytics.product.stats');

        // Operations Dashboard
        Route::get('operations', 'operations')->name('admin.analytics.operations.index');
        Route::get('operations/stats', 'operationsStats')->name('admin.analytics.operations.stats');

        // Menu Analytics Dashboard
        Route::get('menu', 'menu')->name('admin.analytics.menu.index');
        Route::get('menu/stats', 'menuStats')->name('admin.analytics.menu.stats');

        // Channels Dashboard
        Route::get('channels', 'channels')->name('admin.analytics.channels.index');
        Route::get('channels/stats', 'channelsStats')->name('admin.analytics.channels.stats');
    });
});
