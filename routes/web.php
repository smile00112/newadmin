<?php

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