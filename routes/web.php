<?php

use App\Http\Controllers\Admin\Analytics\AnalyticsEventController;
use App\Http\Controllers\ApiTestController;
use App\Http\Controllers\OrderStatusCronController;
use Illuminate\Support\Facades\Route;

Route::get('/api-test', [ApiTestController::class, 'index'])->name('api.test');
Route::get('/cron/orders/pending-to-preparing', [OrderStatusCronController::class, 'pendingToPreparing'])
    ->name('cron.orders.pending-to-preparing');
Route::get('/cron/orders/preparing-to-ready', [OrderStatusCronController::class, 'preparingToReady'])
    ->name('cron.orders.preparing-to-ready');
Route::get('/cron/orders/ready-to-completed', [OrderStatusCronController::class, 'readyToCompleted'])
    ->name('cron.orders.ready-to-completed');
/*
|--------------------------------------------------------------------------
| Analytics Event Ingestion API
|--------------------------------------------------------------------------
*/
Route::prefix('api/analytics')->middleware('throttle:120,1')->group(function () {
    Route::post('events', [AnalyticsEventController::class, 'store'])->name('api.analytics.events.store');
    Route::post('events/batch', [AnalyticsEventController::class, 'storeBatch'])->name('api.analytics.events.batch');
    Route::post('sessions', [AnalyticsEventController::class, 'startSession'])->name('api.analytics.sessions.store');
});
