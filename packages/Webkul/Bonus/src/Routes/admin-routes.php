<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\ConfigurationController;
use Webkul\Bonus\Http\Controllers\Admin\BonusLevelController;
use Webkul\Bonus\Http\Controllers\Admin\BonusManageController;
use Webkul\Bonus\Http\Controllers\Admin\BonusSettingController;
use Webkul\Bonus\Http\Controllers\Admin\BonusTransactionController;

Route::group( ['prefix' => config('app.admin_url')], function () {
    Route::get('configuration/bonus/general', [ConfigurationController::class, 'index'])
        ->defaults('slug', 'bonus')
        ->defaults('slug2', 'general')
        ->name('admin.bonus.settings.index');

    Route::prefix('bonus')->group(function () {
        Route::get('levels', [BonusLevelController::class, 'index'])->name('admin.bonus.levels.index');
        Route::get('levels/create', [BonusLevelController::class, 'create'])->name('admin.bonus.levels.create');
        Route::post('levels', [BonusLevelController::class, 'store'])->name('admin.bonus.levels.store');
        Route::get('levels/{id}/edit', [BonusLevelController::class, 'edit'])->name('admin.bonus.levels.edit');
        Route::put('levels/{id}', [BonusLevelController::class, 'update'])->name('admin.bonus.levels.update');
        Route::delete('levels/{id}', [BonusLevelController::class, 'destroy'])->name('admin.bonus.levels.destroy');

        Route::get('transactions', [BonusTransactionController::class, 'index'])->name('admin.bonus.transactions.index');

        Route::prefix('manage')->controller(BonusManageController::class)->group(function () {
            Route::get('recent-accruals', 'getRecentAccruals')->name('admin.bonus.manage.recent-accruals');
            Route::get('search-customer', 'searchCustomer')->name('admin.bonus.manage.search-customer');
            Route::get('customer/{id}', 'getCustomerInfo')->name('admin.bonus.manage.customer-info');
            Route::post('add-bonus', 'addBonus')->name('admin.bonus.manage.add-bonus');
            Route::post('deduct-bonus', 'deductBonus')->name('admin.bonus.manage.deduct-bonus');
        });
    });
});
