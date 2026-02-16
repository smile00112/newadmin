<?php

namespace Webkul\TochkaPayment\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Events\PaymentFailed;
use Webkul\TochkaPayment\Services\TelegramNotificationService;

class SendTelegramNotificationOnPaymentFailed
{
    /**
     * Handle the event.
     *
     * @param  \Webkul\TochkaPayment\Events\PaymentFailed  $event
     * @return void
     */
    public function handle(PaymentFailed $event): void
    {
        try {
            $telegramService = new TelegramNotificationService();
            $telegramService->sendPaymentNotification($event->payment, false);
        } catch (\Exception $e) {
            // Log error but don't fail event processing
            Log::error('Tochka Payment: Failed to send Telegram notification on payment failed', [
                'payment_id' => $event->payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
