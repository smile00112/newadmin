<?php

namespace Webkul\RestApi\Listeners;

use Illuminate\Database\Eloquent\Model;
use Webkul\RestApi\Services\CustomerOrdersCache;
use Webkul\Sales\Models\Order;

class InvalidateCustomerOrdersCache
{
    /**
     * Handle checkout.order.save.after - new order created.
     */
    public function onOrderCreated(Order $order): void
    {
        $this->invalidateForOrder($order);
    }

    /**
     * Handle sales.order.update-status.after - order status changed.
     */
    public function onOrderStatusUpdated(Order $order): void
    {
        $this->invalidateForOrder($order);
    }

    /**
     * Handle sales.order.cancel.after - order canceled.
     */
    public function onOrderCanceled(Order $order): void
    {
        $this->invalidateForOrder($order);
    }

    /**
     * Handle eloquent.deleted - order deleted.
     */
    public function onOrderDeleted(Model $model): void
    {
        if ($model instanceof Order) {
            $this->invalidateForOrder($model);
        }
    }

    /**
     * Invalidate cache for the order's customer.
     */
    protected function invalidateForOrder(Order $order): void
    {
        $customerId = $order->customer_id;

        if ($customerId !== null) {
            CustomerOrdersCache::invalidate((int) $customerId);
        }
    }
}
