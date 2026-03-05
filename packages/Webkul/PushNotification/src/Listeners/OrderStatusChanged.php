<?php

namespace Webkul\PushNotification\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\PushNotification\Jobs\SendPushNotificationJob;
use Webkul\PushNotification\Services\FirebasePushService;
use Webkul\Sales\Models\Order;

class OrderStatusChanged
{
    /**
     * Create the event listener.
     */
    public function __construct(protected FirebasePushService $pushService) {}

    /**
     * Handle the event.
     */
    public function handle(Order $order): void
    {
        try {
            if (! $this->pushService->isEnabled()) {
                return;
            }

            if (! $order->customer_id) {
                return;
            }

            if (! $this->pushService->isStatusEnabled($order->status)) {
                return;
            }

            $message = $this->pushService->getMessageForStatus($order->status, $order);
            if (! $message) {
                return;
            }

            SendPushNotificationJob::dispatch(
                $order->customer_id,
                $message['title'],
                $message['body'],
                [
                    'order_id' => (string) $order->id,
                    'status'   => $order->status,
                ]
            );

            Log::debug('Push notification job dispatched', [
                'order_id'    => $order->id,
                'customer_id' => $order->customer_id,
                'status'      => $order->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in OrderStatusChanged push listener', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
