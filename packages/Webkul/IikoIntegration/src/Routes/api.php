<?php

use Illuminate\Support\Facades\Route;
use Webkul\IikoIntegration\Http\Controllers\IikoWebhookController;

/**
 * iiko Integration API routes.
 */
Route::prefix('v1/iiko')->group(function () {
    Route::post('webhook', [IikoWebhookController::class, 'handleWebhook']);
});
