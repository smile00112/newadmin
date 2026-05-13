<?php

use Illuminate\Support\Facades\Route;
use Webkul\PushNotification\Http\Controllers\Api\PushCampaignOpenController;
use Webkul\PushNotification\Http\Controllers\Api\PushCampaignReadController;
use Webkul\PushNotification\Http\Controllers\Api\PushTokenController;

Route::group([
    'middleware' => ['auth:sanctum', 'sanctum.customer'],
    'prefix'    => 'api/v1',
], function () {
    Route::controller(PushTokenController::class)->prefix('customer/push-token')->group(function () {
        Route::post('', 'store');
        Route::delete('', 'destroy');
    });

    Route::post('push/campaign/{campaignId}/open', PushCampaignOpenController::class)
        ->whereNumber('campaignId');

    Route::post('push/campaign/{campaignId}/read', PushCampaignReadController::class)
        ->whereNumber('campaignId');
});
