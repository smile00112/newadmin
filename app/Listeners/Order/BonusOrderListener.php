<?php

namespace App\Listeners\Order;

use App\Services\BonusService;
use App\Services\BonusPaymentService;
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
        if (! core()->getConfigData('bonus_system.general.enabled')) {
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
        $accrualStatuses = core()->getConfigData('bonus_system.general.accrual_status');
        
        if (is_array($accrualStatuses) && in_array($order->status, $accrualStatuses)) {
            $this->accrueBonusesForOrder($order, $customer);
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
        if (! core()->getConfigData('bonus_system.general.enabled')) {
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
        $accrualStatuses = core()->getConfigData('bonus_system.general.accrual_status');
        if (is_array($accrualStatuses) && in_array($status, $accrualStatuses)) {
            // Only accrue if not already accrued
            if ($order->base_bonus_accrued == 0) {
                $this->accrueBonusesForOrder($order, $customer);
            }
        }

        // Check refund statuses
        $refundStatuses = core()->getConfigData('bonus_system.general.refund_status');
        if (is_array($refundStatuses) && in_array($status, $refundStatuses)) {
            // Refund bonuses if they were used
            if ($order->base_bonus_amount > 0) {
                $this->bonusService->refundBonus($customer, $order->base_bonus_amount, $order);
                
                // Reset order bonus amount
                $order->update([
                    'bonus_amount' => 0,
                    'base_bonus_amount' => 0,
                ]);
            }

            // Refund accrued bonuses if any
            if ($order->base_bonus_accrued > 0) {
                // Note: We don't refund accrued bonuses, we just don't count them
                // But if needed, we can create a deduction record
            }
        }
    }

    /**
     * Accrue bonuses for order.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @return void
     */
    protected function accrueBonusesForOrder($order, $customer): void
    {
        $cashback = $this->bonusService->calculateCashback($order, $customer);

        if ($cashback > 0) {
            $this->bonusService->accrueBonus($customer, $cashback, $order);

            // Update order
            $order->update([
                'bonus_accrued' => core()->convertPrice($cashback),
                'base_bonus_accrued' => $cashback,
            ]);
        }
    }
}
