<?php

namespace Webkul\IikoIntegration\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Jobs\CancelOrderInIikoJob;
use Webkul\IikoIntegration\Jobs\SyncOrderToIikoJob;
use Webkul\IikoIntegration\Services\IikoOrderService;
use Webkul\Sales\Models\Order;

class OrderSyncListener
{
    public function __construct(
        protected IikoOrderService $orderService
    ) {}

    /**
     * Handle order created event — dispatches to queue.
     */
    public function handleOrderCreated($order): void
    {
        if (! $order instanceof Order) {
            return;
        }

        try {
            SyncOrderToIikoJob::dispatch($order->id);
        } catch (\Exception $e) {
            Log::error('iiko: Failed to dispatch sync job', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle order cancelled event — dispatches to queue (runs after response when sync driver).
     */
    public function handleOrderCancelled($order): void
    {
        if (! $order instanceof Order) {
            return;
        }

        try {
            CancelOrderInIikoJob::dispatch($order->id)->afterResponse();
        } catch (\Exception $e) {
            Log::error('iiko: Failed to dispatch cancel job', [
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
        if (! $order instanceof Order) {
            return;
        }

        try {
            $this->orderService->updateOrderStatus($order, $order->status);
        } catch (\Exception $e) {
            Log::error('iiko: Failed to update order status', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
        }
    }
}
