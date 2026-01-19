<?php

namespace Webkul\IikoIntegration\Listeners;

use Webkul\IikoIntegration\Services\IikoOrderService;
use Webkul\Sales\Models\Order;

class OrderSyncListener
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected IikoOrderService $orderService
    ) {}

    /**
     * Handle order created event.
     */
    public function handleOrderCreated($order): void
    {
        if (!$order instanceof Order) {
            return;
        }

        // Sync order to iiko asynchronously or synchronously
        // For now, we'll do it synchronously, but can be queued later
        try {
            $this->orderService->syncOrderToIiko($order);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('iiko: Failed to sync order on create', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle order cancelled event.
     */
    public function handleOrderCancelled($order): void
    {
        if (!$order instanceof Order) {
            return;
        }

        try {
            $this->orderService->cancelOrderInIiko($order);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('iiko: Failed to cancel order', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle order status updated event.
     */
    public function handleOrderStatusUpdated($order): void
    {
        if (!$order instanceof Order) {
            return;
        }

        try {
            $this->orderService->updateOrderStatus($order, $order->status);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('iiko: Failed to update order status', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
        }
    }
}
