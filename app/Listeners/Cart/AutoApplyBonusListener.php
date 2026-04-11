<?php

namespace App\Listeners\Cart;

use App\Services\BonusPaymentService;
use Webkul\Bonus\Services\BonusService;
use Webkul\Checkout\Contracts\Cart;
use Webkul\Checkout\Contracts\CartItem;
use Webkul\Checkout\Facades\Cart as CartFacade;

class AutoApplyBonusListener
{
    /**
     * Prevents infinite recursion when this listener calls Cart::collectTotals().
     */
    protected static bool $recalculatingAutoApplyBonus = false;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        protected BonusPaymentService $bonusPaymentService,
        protected BonusService $bonusService
    ) {}

    /**
     * Handle the event - recalculate bonus if auto_apply is enabled.
     *
     * @param  \Webkul\Checkout\Contracts\Cart|\Webkul\Checkout\Contracts\CartItem  $cartOrItem
     * @return void
     */
    public function handle($cartOrItem): void
    {
        // Check if bonus system is enabled
        if (! $this->bonusService->isEnabled()) {
            return;
        }

        // Get cart from event parameter
        $cart = $this->getCartFromEvent($cartOrItem);

        if (! $cart) {
            return;
        }

        // Check if auto_apply is enabled
        if (! $cart->auto_apply) {
            return;
        }

        // Check if cart has a customer
        if (! $cart->customer_id) {
            return;
        }

        if (self::$recalculatingAutoApplyBonus) {
            return;
        }

        self::$recalculatingAutoApplyBonus = true;

        try {
            // Recalculate maximum bonus amount
            $maxAmount = $this->bonusService->getMaxUsableBonuses($cart, (int) $cart->customer_id);

            // Apply the recalculated bonus amount
            $this->bonusPaymentService->applyBonusToCart($cart, $maxAmount);

            // Recalculate totals
            CartFacade::collectTotals();
        } catch (\Exception $e) {
            // Log error but don't break the cart update process
            report($e);
        } finally {
            self::$recalculatingAutoApplyBonus = false;
        }
    }

    /**
     * Get cart from event parameter.
     *
     * @param  \Webkul\Checkout\Contracts\Cart|\Webkul\Checkout\Contracts\CartItem  $cartOrItem
     * @return \Webkul\Checkout\Contracts\Cart|null
     */
    protected function getCartFromEvent($cartOrItem): ?Cart
    {
        // If it's already a Cart, return it
        if ($cartOrItem instanceof Cart) {
            return $cartOrItem;
        }

        // If it's a CartItem, get the cart from it
        if ($cartOrItem instanceof CartItem) {
            return $cartOrItem->cart;
        }

        return null;
    }
}
