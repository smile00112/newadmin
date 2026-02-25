<?php

declare(strict_types=1);

namespace Webkul\Newsletters\Services;

use Illuminate\Support\Facades\Log;
use Webkul\ExternalPayments\Services\Adapters\TochkaPaymentAdapter;
use Webkul\Newsletters\Models\AccountTopup;
use Webkul\Newsletters\Repositories\AccountTopupRepository;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Webkul\User\Models\Admin;

final class RegistrationPaymentService
{
    public function __construct(
        protected TochkaPaymentAdapter $tochkaAdapter,
        protected CompanyAccountRepository $accountRepository,
        protected AccountTopupRepository $topupRepository
    ) {}

    /**
     * Create payment for registration topup and return payment URL.
     *
     * @return array{payment_url: string, payment_id: int}
     * @throws \Throwable
     */
    public function createPayment(Admin $admin, float $amount): array
    {
        $companyId = (int) $admin->company_id;
        $account = $this->accountRepository->getOrCreateForCompany($companyId);

        $topup = $this->topupRepository->create([
            'account_id' => $account->id,
            'type' => AccountTopup::TYPE_TOPUP,
            'amount' => $amount,
            'status' => AccountTopup::STATUS_PENDING,
            'admin_id' => $admin->id,
            'notes' => 'Пополнение при регистрации',
            'is_registration' => true,
        ]);

        $basePath = '/mailing-service/register/payment/success';
        $failPath = '/payment/tochka/fail';

        $paymentData = [
            'amount' => $amount,
            'client_name' => $admin->name,
            'client_email' => $admin->email,
            'client_phone' => '',
            'company_id' => $companyId,
            'external_order_id' => sprintf('newsletter_registration_%d', $topup->id),
            'product_name' => 'Пополнение счёта при регистрации',
            'success_redirect_path' => $basePath,
            'fail_redirect_path' => $failPath,
        ];

        try {
            $result = $this->tochkaAdapter->createPayment($paymentData);

            $providerPaymentId = (string) ($result['payment_id'] ?? '');
            $paymentUrl = (string) ($result['payment_url'] ?? '');

            if ($providerPaymentId === '' || $paymentUrl === '') {
                Log::error('Registration payment: provider response missing required fields', [
                    'topup_id' => $topup->id,
                    'response' => $result,
                ]);
                throw new \RuntimeException('Платёжный провайдер не вернул данные для оплаты.');
            }

            $topup->update([
                'provider_key' => 'tochka',
                'provider_payment_id' => $providerPaymentId,
                'payment_url' => $paymentUrl,
            ]);

            return [
                'payment_url' => $paymentUrl,
                'payment_id' => (int) $providerPaymentId,
            ];
        } catch (\Throwable $e) {
            $topup->update([
                'status' => AccountTopup::STATUS_FAILED,
                'notes' => trim(($topup->notes ? $topup->notes . PHP_EOL : '') . $e->getMessage()),
            ]);
            throw $e;
        }
    }
}
