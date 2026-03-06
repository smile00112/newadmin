<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\AddressController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\AuthController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\BonusController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\CartController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\CheckoutController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\GDPRController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\InvoiceController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\MultiChannelAuthController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\NewsLetterController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\OrderController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\SavedCardController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\ShipmentController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\TransactionController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\WishlistController;
use Webkul\RestApi\Http\Controllers\V1\Shop\TelegramWebhookController;

/**
 * Customer unauthorized routes.
 */
Route::controller(AuthController::class)->prefix('customer')->group(function () {
    Route::post('login', 'login');

    Route::post('register', 'register');

    Route::post('forgot-password', 'forgotPassword');
});

/**
 * News Letter routes.
 */
Route::controller(NewsLetterController::class)->prefix('customer/subscription')->group(function () {
    Route::post('', 'store');
});

/**
 * Multi-channel authentication routes.
 */
Route::controller(MultiChannelAuthController::class)->prefix('customer/auth')->group(function () {
    Route::post('sms/initiate', 'initiateSmsAuth');
    Route::post('whatsapp/initiate', 'initiateWhatsAppAuth');
    Route::post('telegram/initiate', 'initiateTelegramAuth');
    Route::post('telegram/initiate-legacy', 'initiateTelegramAuthLegacy');
    Route::post('verify', 'verifyAndAuthenticate');
    Route::post('reset-token', 'resetToken');
    Route::post('verify-reset', 'verifyResetAndGenerateToken');
});

/**
 * Telegram webhook route (public, no CSRF).
 */
Route::post('telegram/webhook', [TelegramWebhookController::class, 'handleWebhook'])
    ->name('api.v1.telegram.webhook');

/**
 * Customer authorized routes.
 */
Route::group(['middleware' => ['auth:sanctum', 'sanctum.customer']], function () {
    /**
     * Broadcasting authorization route for private channels.
     */
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });

    /**
     * Customer auth routes.
     */
    Route::controller(AuthController::class)->prefix('customer')->group(function () {
        Route::get('get', 'get');

        Route::put('profile', 'update');

        Route::post('logout', 'logout');
    });

    /**
     * Customer address routes.
     */
    Route::controller(AddressController::class)->prefix('customer/addresses')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');

        Route::post('', 'store');

        Route::put('{id}', 'update');

        Route::patch('make-default/{id}', 'makeDefault');

        Route::delete('{id}', 'destroy');
    });

    /**
     * Customer sale orders routes.
     */
    Route::controller(OrderController::class)->prefix('customer/orders')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');

        Route::post('{id}/cancel', 'cancel');

        Route::post('{id}/rate', 'rate');

        Route::get('reorder/{id}', 'reorder');
    });

    /**
     * Customer sale orders filtered routes.
     */
    Route::controller(OrderController::class)->prefix('customer')->group(function () {
        Route::get('active-orders', 'activeOrders');

        Route::get('completed-orders', 'completedOrders');

        Route::get('cancelled-orders', 'cancelledOrders');

        Route::get('canselled-orders', 'cancelledOrders'); // Alias for typo compatibility
    });

    /**
     * Customer sale invoices routes.
     */
    Route::controller(InvoiceController::class)->prefix('customer/invoices')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');
    });

    /**
     * Customer sale shipment routes.
     */
    Route::controller(ShipmentController::class)->prefix('customer/shipments')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');
    });

    /**
     * Customer sale transaction routes.
     */
    Route::controller(TransactionController::class)->prefix('customer/transactions')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');
    });

    /**
     * Customer saved cards routes.
     */
    Route::controller(SavedCardController::class)->prefix('customer/saved-cards')->group(function () {
        Route::get('', 'index');
        Route::delete('{id}', 'destroy');
    });

    /**
     * Customer wishlist routes.
     */
    Route::controller(WishlistController::class)->prefix('customer/wishlist')->group(function () {
        Route::get('', 'index');

        Route::post('{id}', 'addOrRemove');

        Route::post('{id}/move-to-cart', 'moveToCart');

        Route::delete('all', 'destroyAll');
    });

    /**
     * Customer cart routes.
     */
    Route::controller(CartController::class)->prefix('customer/cart')->group(function () {
        Route::get('', 'index');

        Route::post('add/{productId}', 'store');

        Route::put('update', 'update');

        Route::delete('remove/{cartItemId}', 'removeItem');

        Route::delete('remove', 'removeAll');

        Route::post('move-to-wishlist/{cartItemId}', 'moveToWishlist');

        Route::post('coupon', 'applyCoupon');

        Route::delete('coupon', 'removeCoupon');

        Route::get('cross-sell', 'crossSellProducts');
    });

    /**
     * Customer checkout routes.
     */
    Route::controller(CheckoutController::class)->prefix('customer/checkout')->group(function () {
        Route::post('save-address', 'saveAddress');

        Route::post('save-shipping', 'saveShipping');

        Route::post('save-payment', 'savePayment');

        Route::post('check-minimum-order', 'checkMinimumOrder');

        Route::post('save-order', 'saveOrder');
    });

    /**
     * Checkout bonus routes.
     */
    Route::controller(CartController::class)->prefix('checkout/bonus')->group(function () {
        Route::post('auto-apply', 'autoApplyBonus');
        Route::delete('auto-apply', 'disableAutoApplyBonus');
    });

    /**
     * Checkout table routes.
     */
    Route::controller(CartController::class)->prefix('checkout/table')->group(function () {
        Route::post('bind', 'bindTable');
        Route::delete('bind', 'unbindTable');
    });

    /**
     * GDPR.
     */
    Route::controller(GDPRController::class)->prefix('customer/gdpr')->group(function () {
        Route::get('', 'allResources');

        Route::get('{id}', 'getResource');

        Route::post('', 'store');

        Route::put('revoke/{id}', 'revoke');
    });

    /**
     * Customer bonus routes (with typo - kept for backward compatibility).
     */
    Route::controller(BonusController::class)->prefix('customer/bonuces')->group(function () {
        Route::get('', 'index');
    });

    /**
     * Customer bonus routes (correct spelling).
     */
    Route::controller(BonusController::class)->prefix('customer/bonuses')->group(function () {
        Route::get('', 'index');
    });

    /**
     * Customer bonus test route.
     */
    Route::controller(BonusController::class)->prefix('customer/bonuces-test')->group(function () {
        Route::get('', 'indexTest');
    });

});
