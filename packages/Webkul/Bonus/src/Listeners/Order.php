<?php

namespace Webkul\Bonus\Listeners;

use Illuminate\Support\Facades\Log;
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
            $status = $order->status;
            $accrualStatus = $this->getNormalizedAccrualStatus();

            Log::info('bonus.listener.after_created.captured', [
                'order_id'       => $order->id,
                'increment_id'   => $order->increment_id,
                'status'         => $status,
                'accrual_status' => $accrualStatus,
                'customer_id'    => $order->customer_id,
            ]);

            // Deduct bonuses if they were used (bonus_amount_used is already set from cart)
            $bonusAmount = $order->base_bonus_amount_used ?? 0;
            if ($bonusAmount > 0) {
                $this->bonusService->deductBonuses($order, $bonusAmount);
            }

            // Accrue bonuses on order creation when configured status matches current status.
            if ($status === $accrualStatus) {
                Log::info('bonus.listener.after_created.accrual_started', [
                    'order_id'       => $order->id,
                    'increment_id'   => $order->increment_id,
                    'status'         => $status,
                    'accrual_status' => $accrualStatus,
                ]);

                $this->bonusService->accrueBonuses($order);
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
            $status = $order->status;

            // Accrue bonuses when order reaches configured status
            $accrualStatus = $this->getNormalizedAccrualStatus();

            Log::info('bonus.listener.after_status_updated.captured', [
                'order_id'       => $order->id,
                'increment_id'   => $order->increment_id,
                'status'         => $status,
                'accrual_status' => $accrualStatus,
                'customer_id'    => $order->customer_id,
            ]);

            if ($status === $accrualStatus) {
                Log::info('bonus.listener.after_status_updated.accrual_started', [
                    'order_id'       => $order->id,
                    'increment_id'   => $order->increment_id,
                    'status'         => $status,
                    'accrual_status' => $accrualStatus,
                ]);

                $this->bonusService->accrueBonuses($order);
            }

            // Return bonuses when order is cancelled via status dropdown
            // (cancel button fires sales.order.cancel.after → afterCanceled,
            //  but status dropdown fires only sales.order.update-status.after)
            if ($status === \Webkul\Sales\Models\Order::STATUS_CANCELED) {
                Log::info('bonus.listener.after_status_updated.returning_bonuses', [
                    'order_id'     => $order->id,
                    'customer_id'  => $order->customer_id,
                ]);

                $this->bonusService->returnBonuses($order);
            } else {
                // Re-deduct bonuses if the order was previously cancelled and is now reactivated
                $this->bonusService->reDeductAfterReactivation($order);
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

    /**
     * Return configured accrual status with backward-compatible typo normalization.
     */
    protected function getNormalizedAccrualStatus(): string
    {
        $accrualStatus = core()->getConfigData('bonus.general.settings.accrual_status');

        // Backward-compatible fix for misspelled value in stored config.
        if ($accrualStatus === 'panding') {
            $accrualStatus = \Webkul\Sales\Models\Order::STATUS_PENDING;
        }

        if (! $accrualStatus) {
            return \Webkul\Sales\Models\Order::STATUS_COMPLETED;
        }

        return $accrualStatus;
    }
}
