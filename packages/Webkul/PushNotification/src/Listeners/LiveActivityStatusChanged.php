<?php

namespace Webkul\PushNotification\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\PushNotification\Jobs\SendLiveActivityPushJob;
use Webkul\PushNotification\Models\OrderLiveActivityToken;
use Webkul\PushNotification\Services\ApnsLiveActivityService;
use Webkul\Sales\Models\Order;

class LiveActivityStatusChanged
{
    /**
     * Create the event listener.
     */
    public function __construct(protected ApnsLiveActivityService $apnsService) {}

    /**
     * Handle the order status changed event.
     */
    public function handle(Order $order): void
    {
        try {
            if (! $this->apnsService->isEnabled()) {
                return;
            }

            if (! $order->customer_id) {
                return;
            }

            $tokenRecord = OrderLiveActivityToken::where('order_id', $order->id)->first();

            if (! $tokenRecord) {
                return;
            }

            SendLiveActivityPushJob::dispatch($order->id, $tokenRecord->id);

            Log::debug('LiveActivityStatusChanged: job dispatched', [
                'order_id'       => $order->id,
                'order_increment' => $order->increment_id,
                'status'         => $order->status,
            ]);
        } catch (\Exception $e) {
            Log::error('LiveActivityStatusChanged: unexpected error', [
                'order_id' => $order->id ?? null,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
