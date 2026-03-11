<?php

use Illuminate\Support\Facades\Route;
use Webkul\Reporting\Http\Controllers\AnalyticsEventController;

Route::prefix('api/analytics')->middleware('throttle:120,1')->group(function () {
    Route::post('events', [AnalyticsEventController::class, 'store'])->name('api.analytics.events.store');
    Route::post('events/batch', [AnalyticsEventController::class, 'storeBatch'])->name('api.analytics.events.batch');
    Route::post('sessions', [AnalyticsEventController::class, 'startSession'])->name('api.analytics.sessions.store');
});
