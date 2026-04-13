<?php

namespace Webkul\RestApi\Listeners;

use Webkul\Sales\Models\Order;
use Webkul\RestApi\Jobs\WarmCustomerBonusesCacheJob;
use Webkul\RestApi\Services\CustomerBonusesCache;

class InvalidateCustomerBonusesCache
{
    /**
     * Handle checkout.order.save.after - new order created.
     */
    public function onOrderCreated(Order $order): void
    {
        $this->invalidateForOrder($order);
        $this->dispatchWarmJobForOrder($order);
    }

    /**
     * Handle sales.order.update-status.after - order status changed.
     */
    public function onOrderStatusUpdated(Order $order): void
    {
        $this->invalidateForOrder($order);
        $this->dispatchWarmJobForOrder($order);
    }

    /**
     * Handle sales.order.cancel.after - order canceled.
     */
    public function onOrderCanceled(Order $order): void
    {
        $this->invalidateForOrder($order);
        $this->dispatchWarmJobForOrder($order);
    }

    /**
     * Handle bonus.balance.changed - bonus balance changed for a customer.
     *
     * @param  int  $customerId
     * @return void
     */
    public function onBalanceChanged(int $customerId): void
    {
        CustomerBonusesCache::invalidate($customerId);
        $this->dispatchWarmJob($customerId);
    }

    /**
     * Invalidate cache for the order's customer.
     */
    protected function invalidateForOrder(Order $order): void
    {
        if ($order->customer_id === null) {
            return;
        }

        CustomerBonusesCache::invalidate((int) $order->customer_id);
    }

    /**
     * Dispatch warmup job for order's customer bonuses cache.
     */
    protected function dispatchWarmJobForOrder(Order $order): void
    {
        if ($order->customer_id === null) {
            return;
        }

        $this->dispatchWarmJob((int) $order->customer_id);
    }

    /**
     * Dispatch job to warm customer bonuses cache via queue.
     */
    protected function dispatchWarmJob(int $customerId): void
    {
        WarmCustomerBonusesCacheJob::dispatch($customerId)
            ->delay(now()->addSeconds(2));
    }
}
