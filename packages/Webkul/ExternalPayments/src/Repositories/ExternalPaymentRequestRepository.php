<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Repositories;

use Webkul\ExternalPayments\Models\ExternalPaymentRequest;

class ExternalPaymentRequestRepository
{
    public function findByProviderPayment(string $paymentProvider, int $providerPaymentId): ?ExternalPaymentRequest
    {
        return ExternalPaymentRequest::where('payment_provider', $paymentProvider)
            ->where('provider_payment_id', $providerPaymentId)
            ->with('externalSystem')
            ->first();
    }

    public function create(array $data): ExternalPaymentRequest
    {
        return ExternalPaymentRequest::create($data);
    }

    public function update(ExternalPaymentRequest $model, array $data): ExternalPaymentRequest
    {
        $model->update($data);

        return $model->fresh();
    }
}
