<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\ApplicationErrorController;

/**
 * Application errors routes.
 */
Route::controller(ApplicationErrorController::class)->prefix('application-errors')->group(function () {
    Route::get('', 'index')->name('admin.application_errors.index');
    Route::get('view/{id}', 'show')->name('admin.application_errors.show');
});
