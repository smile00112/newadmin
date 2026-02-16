<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Listeners;

use Webkul\ExternalPayments\Services\ExternalPaymentWebhookSender;

class SendExternalPaymentWebhookListener
{
    public function __construct(
        protected ExternalPaymentWebhookSender $webhookSender
    ) {}

    /**
     * Handle payment success event. Called with ($payment) from Event::dispatch('external_payments.payment.success', [$payment]).
     */
    public function handlePaymentSuccess(object $payment): void
    {
        $id = $payment->id ?? null;
        if ($id === null) {
            return;
        }

        $this->webhookSender->handlePaymentSuccess('tochka', (int) $id, $payment);
    }
}
