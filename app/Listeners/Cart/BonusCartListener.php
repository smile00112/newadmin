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
        $bonusService = app(\Webkul\Bonus\Services\BonusService::class);
        if (! $bonusService->isEnabled()) {
            return;
        }

        // Apply bonus discount to cart totals
        // Note: Бонусы уже вычитаются из grand_total в методе collectTotals()
        // Этот listener служит как дополнительная проверка и может использоваться
        // для других операций с бонусами после пересчета итогов
        if ($cart->base_bonus_amount > 0) {
            // Убеждаемся, что бонусы правильно учтены в grand_total
            // (основная логика уже выполнена в collectTotals())
            $cart->refresh();
        }
    }
}
