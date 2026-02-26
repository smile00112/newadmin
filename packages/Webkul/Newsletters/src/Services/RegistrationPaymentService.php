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
        Log::channel('single')->info('[Registration Payment] Step 8a: createPayment started', [
            'admin_id' => $admin->id,
            'amount' => $amount,
        ]);

        $companyId = (int) $admin->company_id;
        $account = $this->accountRepository->getOrCreateForCompany($companyId);

        Log::channel('single')->info('[Registration Payment] Step 8b: Account resolved', [
            'admin_id' => $admin->id,
            'account_id' => $account->id,
        ]);

        $topup = $this->topupRepository->create([
            'account_id' => $account->id,
            'type' => AccountTopup::TYPE_TOPUP,
            'amount' => $amount,
            'status' => AccountTopup::STATUS_PENDING,
            'admin_id' => $admin->id,
            'notes' => 'Пополнение при регистрации',
            'is_registration' => true,
        ]);

        Log::channel('single')->info('[Registration Payment] Step 8c: Topup created', [
            'topup_id' => $topup->id,
            'account_id' => $account->id,
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

        Log::channel('single')->info('[Registration Payment] Step 8d: Calling Tochka API', [
            'topup_id' => $topup->id,
            'external_order_id' => $paymentData['external_order_id'],
        ]);

        try {
            $result = $this->tochkaAdapter->createPayment($paymentData);

            $providerPaymentId = (string) ($result['payment_id'] ?? '');
            $paymentUrl = (string) ($result['payment_url'] ?? '');

            if ($providerPaymentId === '' || $paymentUrl === '') {
                Log::channel('single')->error('[Registration Payment] Step 8e FAILED: Provider response missing required fields', [
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

            Log::channel('single')->info('[Registration Payment] Step 8e: Payment created successfully', [
                'topup_id' => $topup->id,
                'provider_payment_id' => $providerPaymentId,
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
