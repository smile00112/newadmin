<?php

namespace App\Listeners;

use App\Jobs\SendPushNotificationJob;
use App\Services\FirebasePushService;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Order;

class SendPushOnOrderStatusChange
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
            // Check if push notifications are enabled
            if (! $this->pushService->isEnabled()) {
                return;
            }

            // Skip if no customer is associated
            if (! $order->customer_id) {
                return;
            }

            // Check if the current status should trigger a push
            if (! $this->pushService->isStatusEnabled($order->status)) {
                return;
            }

            // Get message for the current status
            $message = $this->pushService->getMessageForStatus($order->status, $order);
            if (! $message) {
                return;
            }

            // Dispatch job to send push notification asynchronously
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
            Log::error('Error in SendPushOnOrderStatusChange listener', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
