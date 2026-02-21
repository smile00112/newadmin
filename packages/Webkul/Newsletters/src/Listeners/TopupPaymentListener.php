<?php

declare(strict_types=1);

namespace Webkul\Newsletters\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Newsletters\Models\AccountTopup;
use Webkul\Newsletters\Models\CompanyAccount;
use Webkul\TochkaPayment\Events\PaymentFailed;

final class TopupPaymentListener
{
    public function handlePaymentSuccess(object $payment): void
    {
        $providerPaymentId = isset($payment->id) ? (string) $payment->id : null;

        if (! $providerPaymentId) {
            return;
        }

        DB::transaction(function () use ($providerPaymentId): void {
            /** @var AccountTopup|null $topup */
            $topup = AccountTopup::query()
                ->where('provider_key', 'tochka')
                ->where('provider_payment_id', $providerPaymentId)
                ->lockForUpdate()
                ->first();

            if (! $topup) {
                Log::warning('Owner account topup payment: topup not found for successful payment', [
                    'provider_key' => 'tochka',
                    'provider_payment_id' => $providerPaymentId,
                ]);

                return;
            }

            if ($topup->status === AccountTopup::STATUS_PAID) {
                return;
            }

            /** @var CompanyAccount $account */
            $account = CompanyAccount::query()
                ->lockForUpdate()
                ->findOrFail($topup->account_id);

            $account->balance = (float) $account->balance + (float) $topup->amount;
            $account->save();

            $topup->status = AccountTopup::STATUS_PAID;
            $topup->paid_at = now();
            $topup->save();
        });
    }

    public function handlePaymentFailed(PaymentFailed $event): void
    {
        $providerPaymentId = isset($event->payment->id) ? (string) $event->payment->id : null;

        if (! $providerPaymentId) {
            return;
        }

        AccountTopup::query()
            ->where('provider_key', 'tochka')
            ->where('provider_payment_id', $providerPaymentId)
            ->where('status', AccountTopup::STATUS_PENDING)
            ->update([
                'status' => AccountTopup::STATUS_FAILED,
            ]);
    }
}
