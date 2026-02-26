<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\ExternalPayments\Jobs\ProcessExternalPaymentRegistrationJob;
use Webkul\ExternalPayments\Repositories\ExternalPaymentRequestRepository;
use Webkul\TochkaPayment\Models\TochkaPaymentHistory;

final class ExternalPaymentRegistrationListener
{
    public function __construct(
        protected ExternalPaymentRequestRepository $paymentRequestRepository
    ) {}

    /**
     * Handle external_payments.payment.success for ExternalPayments flow.
     * Dispatches deferred job to create admin and send notifications.
     */
    public function handlePaymentSuccess(object $payment): void
    {
        $paymentId = $payment->id ?? null;
        if ($paymentId === null) {
            return;
        }

        $externalRequest = $this->paymentRequestRepository->findByProviderPayment('tochka', (int) $paymentId);
        if (! $externalRequest) {
            return;
        }

        if ($payment->status !== TochkaPaymentHistory::STATUS_PAID) {
            return;
        }

        if (empty($payment->client_email) || ! filter_var($payment->client_email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('ExternalPaymentRegistrationListener: Missing or invalid client_email', [
                'payment_id' => $paymentId,
            ]);

            return;
        }

        ProcessExternalPaymentRegistrationJob::dispatch((int) $paymentId);

        Log::info('ExternalPaymentRegistrationListener: Dispatched ProcessExternalPaymentRegistrationJob', [
            'payment_id' => $paymentId,
            'external_request_id' => $externalRequest->id,
        ]);
    }
}
