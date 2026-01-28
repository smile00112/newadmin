<?php

use Illuminate\Support\Facades\Route;
use Webkul\Bonus\Http\Controllers\Admin\BonusLevelController;
use Webkul\Bonus\Http\Controllers\Admin\BonusSettingController;
use Webkul\Bonus\Http\Controllers\Admin\BonusTransactionController;

Route::group(['prefix' => config('app.admin_url')], function () {
    Route::prefix('bonus')->group(function () {
        Route::get('settings', [BonusSettingController::class, 'index'])->name('admin.bonus.settings.index');
        Route::post('settings', [BonusSettingController::class, 'store'])->name('admin.bonus.settings.store');

        Route::get('levels', [BonusLevelController::class, 'index'])->name('admin.bonus.levels.index');
        Route::get('levels/create', [BonusLevelController::class, 'create'])->name('admin.bonus.levels.create');
        Route::post('levels', [BonusLevelController::class, 'store'])->name('admin.bonus.levels.store');
        Route::get('levels/{id}/edit', [BonusLevelController::class, 'edit'])->name('admin.bonus.levels.edit');
        Route::put('levels/{id}', [BonusLevelController::class, 'update'])->name('admin.bonus.levels.update');
        Route::delete('levels/{id}', [BonusLevelController::class, 'destroy'])->name('admin.bonus.levels.destroy');

        Route::get('transactions', [BonusTransactionController::class, 'index'])->name('admin.bonus.transactions.index');
    });
});
