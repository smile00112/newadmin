<?php

declare(strict_types=1);

namespace Webkul\Newsletters\Services;

use Illuminate\Support\Facades\Log;
use Webkul\ExternalPayments\Models\ExternalSystem;
use Webkul\ExternalPayments\Services\PaymentProviderRegistry;
use Webkul\Newsletters\Models\AccountTopup;
use Webkul\User\Models\Admin;

final class TopupPaymentService
{
    public function __construct(
        private readonly PaymentProviderRegistry $providerRegistry
    ) {}

    /**
     * @return array{provider_key: string, provider_payment_id: string, payment_url: string}
     */
    public function createPaymentSession(AccountTopup $topup, Admin $admin, int $companyId): array
    {
        $externalSystem = ExternalSystem::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with('paymentProviders')
            ->first();

        if (! $externalSystem) {
            throw new \RuntimeException('No active external payment system configured for this company.');
        }

        $providerKey = $externalSystem->default_provider;
        if (! $providerKey) {
            throw new \RuntimeException('Default payment provider is not configured for this company.');
        }

        $allowed = $externalSystem->paymentProviders()
            ->where('payment_provider', $providerKey)
            ->exists();

        if (! $allowed) {
            throw new \RuntimeException('Default payment provider is not allowed for this company.');
        }

        if (! $this->providerRegistry->has($providerKey)) {
            throw new \RuntimeException(sprintf('Payment provider "%s" is not registered.', $providerKey));
        }

        $adapter = $this->providerRegistry->get($providerKey);

        $result = $adapter->createPayment([
            'amount'            => (float) $topup->amount,
            'client_name'       => $admin->name,
            'client_email'      => $admin->email,
            'client_phone'      => '',
            'external_order_id' => sprintf('newsletter_topup_%d', $topup->id),
            'product_name'      => 'Пополнение счета рассылок',
            'company_id'        => $companyId,
        ]);

        $providerPaymentId = (string) ($result['payment_id'] ?? '');
        $paymentUrl = (string) ($result['payment_url'] ?? '');

        if ($providerPaymentId === '' || $paymentUrl === '') {
            Log::error('Owner account topup payment: provider response is missing required fields', [
                'topup_id' => $topup->id,
                'provider_key' => $providerKey,
                'response' => $result,
            ]);

            throw new \RuntimeException('Payment provider did not return payment session data.');
        }

        return [
            'provider_key' => $providerKey,
            'provider_payment_id' => $providerPaymentId,
            'payment_url' => $paymentUrl,
        ];
    }
}
