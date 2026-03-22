<?php

use App\Http\Controllers\ApiTestController;
use App\Http\Controllers\OrderStatusCronController;
use Illuminate\Support\Facades\Route;

Route::get('/api-test', [ApiTestController::class, 'index'])->name('api.test');

Route::get('/test/reverb', function () {
    $path = base_path('test/reverb/index.html');

    abort_unless(is_file($path), 404);

    return response()->file($path, [
        'Content-Type' => 'text/html; charset=UTF-8',
    ]);
})->name('test.reverb');
Route::get('/cron/orders/pending-to-preparing', [OrderStatusCronController::class, 'pendingToPreparing'])
    ->name('cron.orders.pending-to-preparing');
Route::get('/cron/orders/preparing-to-ready', [OrderStatusCronController::class, 'preparingToReady'])
    ->name('cron.orders.preparing-to-ready');
Route::get('/cron/orders/ready-to-completed', [OrderStatusCronController::class, 'readyToCompleted'])
    ->name('cron.orders.ready-to-completed');
