<?php

namespace App\Listeners\Order;

use App\Services\BonusPaymentService;
use Webkul\Bonus\Services\BonusService;
use Webkul\Sales\Models\OrderProxy;
use Webkul\Customer\Models\CustomerProxy;

class BonusOrderListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        protected BonusService $bonusService,
        protected BonusPaymentService $bonusPaymentService
    ) {}

    /**
     * Handle order created event.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function handleOrderCreated($order): void
    {
        if (! $this->bonusService->isEnabled()) {
            return;
        }

        if (! $order->customer_id) {
            return;
        }

        $customer = CustomerProxy::find($order->customer_id);

        if (! $customer) {
            return;
        }

        // Process bonus payment if used
        if ($order->base_bonus_amount > 0) {
            $this->bonusPaymentService->processBonusPayment($order, $order->base_bonus_amount);
        }

        // Update customer statistics
        $customer->increment('bonus_total_orders');
        $customer->increment('bonus_total_spent', $order->base_grand_total);

        // Recalculate customer level
        $this->bonusService->updateCustomerLevel($customer);

        // Check if we need to accrue bonuses immediately
        $accrualStatuses = core()->getConfigData('bonus.general.settings.accrual_status');
        
        if (is_array($accrualStatuses) && in_array($order->status, $accrualStatuses)) {
            $this->bonusService->accrueBonuses($order);
        }
    }

    /**
     * Handle order status updated event.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function handleOrderStatusUpdated($order): void
    {
        if (! $this->bonusService->isEnabled()) {
            return;
        }

        if (! $order->customer_id) {
            return;
        }

        $customer = CustomerProxy::find($order->customer_id);

        if (! $customer) {
            return;
        }

        $status = $order->status;

        // Check accrual statuses
        $accrualStatuses = core()->getConfigData('bonus.general.settings.accrual_status');
        if (is_array($accrualStatuses) && in_array($status, $accrualStatuses)) {
            // Only accrue if not already accrued
            if ($order->base_bonus_amount_accrued == 0) {
                $this->bonusService->accrueBonuses($order);
            }
        }

        // Check refund statuses
        $refundStatuses = core()->getConfigData('bonus.general.settings.refund_status');
        if (is_array($refundStatuses) && in_array($status, $refundStatuses)) {
            // Return bonuses if they were used
            if ($order->base_bonus_amount_used > 0) {
                $this->bonusService->returnBonuses($order);
                
                // Reset order bonus amount
                $order->update([
                    'bonus_amount' => 0,
                    'base_bonus_amount' => 0,
                ]);
            }
        }
    }

}
