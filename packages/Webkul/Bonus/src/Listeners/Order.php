<?php

namespace Webkul\Bonus\Listeners;

use Webkul\Bonus\Services\BonusService;
use Webkul\Sales\Contracts\Order as OrderContract;

class Order
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected BonusService $bonusService
    ) {}

    /**
     * Handle order creation event.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function afterCreated(OrderContract $order)
    {
        try {
            // Deduct bonuses if they were used (bonus_amount_used is already set from cart)
            $bonusAmount = $order->base_bonus_amount_used ?? 0;
            if ($bonusAmount > 0) {
                $this->bonusService->deductBonuses($order, $bonusAmount);
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Handle order status update event.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function afterStatusUpdated(OrderContract $order)
    {
        try {
            // Accrue bonuses when order is completed
            if ($order->status === \Webkul\Sales\Models\Order::STATUS_COMPLETED) {
                $this->bonusService->accrueBonuses($order);
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Handle order cancellation event.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function afterCanceled(OrderContract $order)
    {
        try {
            // Return bonuses when order is cancelled
            $this->bonusService->returnBonuses($order);
        } catch (\Exception $e) {
            report($e);
        }
    }
}
