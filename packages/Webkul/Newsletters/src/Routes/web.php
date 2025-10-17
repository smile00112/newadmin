<?php

use Illuminate\Support\Facades\Route;
use Webkul\Newsletters\Http\Controllers\Admin\HooksController;
use Webkul\Newsletters\Http\Controllers\Admin\VacapInstanceController;
use Webkul\Newsletters\Http\Controllers\Admin\MailingListController;
use Webkul\Newsletters\Http\Controllers\Admin\CustomerNumberController;
use Webkul\Newsletters\Http\Controllers\Admin\StopListController;
use Webkul\Newsletters\Http\Controllers\Admin\UnifiedNewsletterController;

Route::group(['prefix' => 'admin/newsletters', 'middleware' => ['web', 'admin']], function () {

    /**
     * Test route to verify module is working.
     */
    Route::get('test', function() {
        return 'Newsletters module is working!';
    })->name('admin.newsletters.test');

    Route::controller(HooksController::class)->prefix('hook')->group(function () {
        Route::get('webhook', 'get_hook')->name('admin.newsletters.hook');
        Route::post('', 'get_hook')->name('admin.newsletters.hook');
    });
    /**
     * Vacap Instances routes.
     */
    Route::controller(VacapInstanceController::class)->prefix('whatsapp-instances')->group(function () {
        Route::get('', 'index')->name('admin.newsletters.whatsapp-instances.index');
        Route::get('create', 'create')->name('admin.newsletters.whatsapp-instances.create');
        Route::post('create', 'store')->name('admin.newsletters.whatsapp-instances.store');
        Route::get('edit/{id}', 'edit')->name('admin.newsletters.whatsapp-instances.edit');
        Route::put('edit/{id}', 'update')->name('admin.newsletters.whatsapp-instances.update');
        Route::delete('{id}', 'destroy')->name('admin.newsletters.whatsapp-instances.destroy');
    });

    /**
     * Mailing Lists routes.
     */
    Route::controller(MailingListController::class)->prefix('mailing-lists')->group(function () {
        Route::get('', 'index')->name('admin.newsletters.mailing-lists.index');
        Route::get('create', 'create')->name('admin.newsletters.mailing-lists.create');
        Route::post('create', 'store')->name('admin.newsletters.mailing-lists.store');
        Route::get('edit/{id}', 'edit')->name('admin.newsletters.mailing-lists.edit');
        Route::put('edit/{id}', 'update')->name('admin.newsletters.mailing-lists.update');
        Route::delete('{id}', 'destroy')->name('admin.newsletters.mailing-lists.destroy');
        Route::post('{id}/send', 'send')->name('admin.newsletters.mailing-lists.send');
    });

    Route::controller(MailingListController::class)->prefix('test')->group(function () {
        Route::get('{id}/test', 'testMailing')->name('admin.newsletters.mailing-lists.test');
    });
    /**
     * Customer Numbers routes.
     */
    Route::controller(CustomerNumberController::class)->prefix('customer-numbers')->group(function () {
        Route::get('', 'index')->name('admin.newsletters.customer-numbers.index');
        Route::get('create', 'create')->name('admin.newsletters.customer-numbers.create');
        Route::post('create', 'store')->name('admin.newsletters.customer-numbers.store');
        Route::get('edit/{id}', 'edit')->name('admin.newsletters.customer-numbers.edit');
        Route::put('edit/{id}', 'update')->name('admin.newsletters.customer-numbers.update');
        Route::delete('{id}', 'destroy')->name('admin.newsletters.customer-numbers.destroy');
        Route::post('import', 'import')->name('admin.newsletters.customer-numbers.import');
    });

    /**
     * Stop List routes.
     */
    Route::controller(StopListController::class)->prefix('stop-list')->group(function () {
        Route::get('', 'index')->name('admin.newsletters.stop-list.index');
        Route::get('create', 'create')->name('admin.newsletters.stop-list.create');
        Route::post('create', 'store')->name('admin.newsletters.stop-list.store');
        Route::get('edit/{id}', 'edit')->name('admin.newsletters.stop-list.edit');
        Route::put('edit/{id}', 'update')->name('admin.newsletters.stop-list.update');
        Route::delete('{id}', 'destroy')->name('admin.newsletters.stop-list.destroy');
        Route::post('check', 'check')->name('admin.newsletters.stop-list.check');
    });

    /**
     * Unified Newsletter Management routes.
     */
    Route::controller(UnifiedNewsletterController::class)->prefix('unified')->group(function () {
        Route::get('', 'index')->name('admin.newsletters.unified.index');
        Route::post('', 'store')->name('admin.newsletters.unified.store');
        Route::get('edit/{id}', 'edit')->name('admin.newsletters.unified.edit');
        Route::put('edit/{id}', 'update')->name('admin.newsletters.unified.update');
        Route::delete('{id}', 'destroy')->name('admin.newsletters.unified.destroy');
    });
});
