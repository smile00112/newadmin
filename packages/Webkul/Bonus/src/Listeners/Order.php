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
            $status = $order->status;

            // Deduct bonuses if they were used (bonus_amount_used is already set from cart)
            $bonusAmount = $order->base_bonus_amount_used ?? 0;
            if ($bonusAmount > 0) {
                $this->bonusService->deductBonuses($order, $bonusAmount);
            }

            // Accrue bonuses on order creation when configured status matches current status.
            if ($status === $this->getNormalizedAccrualStatus()) {
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
            
            // Check deduction statuses - списание бонусов при смене статуса
            $deductionStatuses = core()->getConfigData('bonus.general.settings.deduction_status');
            $deductionStatuses = is_array($deductionStatuses) ? $deductionStatuses : ($deductionStatuses ? [$deductionStatuses] : []);
            if (in_array($status, $deductionStatuses)) {
                // Списать бонусы, если они были использованы, но еще не списаны
                $bonusAmount = $order->base_bonus_amount ?? 0;
                if ($bonusAmount > 0 && ($order->base_bonus_amount_used ?? 0) == 0) {
                    $this->bonusService->deductBonuses($order, $bonusAmount);
                }
            }
            
            // Accrue bonuses when order reaches configured status
            $accrualStatus = $this->getNormalizedAccrualStatus();

            if ($status === $accrualStatus) {
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
