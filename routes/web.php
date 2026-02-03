<?php

use App\Http\Controllers\ApiTestController;
use Illuminate\Support\Facades\Route;

Route::get('/api-test', [ApiTestController::class, 'index'])->name('api.test');
