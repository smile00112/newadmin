<?php

namespace App\Listeners\Cart;

use App\Services\BonusPaymentService;
use Webkul\Checkout\Contracts\Cart;

class BonusCartListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        protected BonusPaymentService $bonusPaymentService
    ) {}

    /**
     * Handle the event - apply bonus discount after totals calculation.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return void
     */
    public function handle(Cart $cart): void
    {
        if (! core()->getConfigData('bonus_system.general.enabled')) {
            return;
        }

        // Apply bonus discount to cart totals
        if ($cart->base_bonus_amount > 0) {
            // Subtract bonus amount from grand total
            $cart->base_grand_total = max(0, $cart->base_grand_total - $cart->base_bonus_amount);
            $cart->grand_total = max(0, $cart->grand_total - $cart->bonus_amount);
            
            // Add bonus to discount amount for display
            $cart->base_discount_amount += $cart->base_bonus_amount;
            $cart->discount_amount += $cart->bonus_amount;
            
            $cart->save();
        }
    }
}
