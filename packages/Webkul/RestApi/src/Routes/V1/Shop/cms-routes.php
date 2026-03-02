<?php

use Illuminate\Support\Facades\Route;
use Webkul\RestApi\Http\Controllers\V1\Shop\CMS\PageController;

/**
 * CMS page routes.
 */
Route::controller(PageController::class)->prefix('cms')->group(function () {
    Route::get('{id}/html', 'getHtmlContent');
});
