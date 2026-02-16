<?php

namespace Webkul\TochkaPayment\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Events\PaymentSuccess;
use Webkul\TochkaPayment\Services\TelegramNotificationService;

class SendTelegramNotificationOnPaymentSuccess
{
    /**
     * Handle the event.
     *
     * @param  \Webkul\TochkaPayment\Events\PaymentSuccess  $event
     * @return void
     */
    public function handle(PaymentSuccess $event): void
    {
        try {
            $telegramService = new TelegramNotificationService();
            $telegramService->sendPaymentNotification($event->payment, true);
        } catch (\Exception $e) {
            // Log error but don't fail event processing
            Log::error('Tochka Payment: Failed to send Telegram notification on payment success', [
                'payment_id' => $event->payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
