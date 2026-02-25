<?php

declare(strict_types=1);

namespace Webkul\Newsletters\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Webkul\Newsletters\Mail\WelcomeAdminNotification;
use Webkul\Newsletters\Models\AccountTopup;

final class RegistrationPaymentListener
{
    /**
     * Handle external_payments.payment.success for registration topups.
     * Sends welcome email with login credentials after successful payment.
     *
     * @param  object  $payment  TochkaPaymentHistory instance
     * @return void
     */
    public function handlePaymentSuccess(object $payment): void
    {
        if (! isset($payment->id)) {
            return;
        }

        $providerPaymentId = (string) $payment->id;

        $topup = AccountTopup::query()
            ->where('provider_key', 'tochka')
            ->where('provider_payment_id', $providerPaymentId)
            ->where('is_registration', true)
            ->with('admin')
            ->first();

        if (! $topup || ! $topup->admin) {
            return;
        }

        if ($topup->status !== AccountTopup::STATUS_PAID) {
            return;
        }

        try {
            $admin = $topup->admin;
            $password = Str::random(12);

            $admin->password = bcrypt($password);
            $admin->save();

            Mail::to($admin->email)->send(new WelcomeAdminNotification($admin, $password));

            Log::info('Registration welcome email sent after payment', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send registration welcome email', [
                'admin_id' => $topup->admin_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            report($e);
        }
    }
}
