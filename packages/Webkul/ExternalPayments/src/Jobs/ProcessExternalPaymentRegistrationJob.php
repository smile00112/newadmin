<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Webkul\Core\Models\CoreConfig;
use Webkul\ExternalPayments\Repositories\ExternalPaymentRequestRepository;
use Webkul\Newsletters\Mail\NewRegistrationNotification;
use Webkul\Newsletters\Mail\WelcomeAdminNotification;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;
use Webkul\User\Repositories\AdminRepository;

final class ProcessExternalPaymentRegistrationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $paymentId
    ) {}

    public function handle(
        ExternalPaymentRequestRepository $paymentRequestRepository,
        AdminRepository $adminRepository
    ): void {
        $payment = TochkaPaymentHistoryProxy::find($this->paymentId);
        if (! $payment || $payment->status !== 'paid') {
            return;
        }

        $externalRequest = $paymentRequestRepository->findByProviderPayment('tochka', (int) $payment->id);
        if (! $externalRequest) {
            return;
        }

        $email = trim($payment->client_email ?? '');
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        if ($adminRepository->findOneByField('email', $email)) {
            Log::info('ProcessExternalPaymentRegistrationJob: Admin already exists', [
                'email' => $email,
            ]);

            return;
        }

        $externalSystem = $externalRequest->externalSystem;
        $companyId = $externalSystem->company_id;
        if (! $companyId) {
            Log::warning('ProcessExternalPaymentRegistrationJob: ExternalSystem has no company_id', [
                'external_system_id' => $externalSystem->id,
            ]);

            return;
        }

        $roleId = (int) config('external-payments.registration.manager_role_id', 4);
        $name = trim($payment->client_name ?? $email) ?: $email;
        $password = Str::random(12);

        try {
            $admin = $adminRepository->create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
                'role_id' => $roleId,
                'company_id' => $companyId,
                'status' => 1,
                'api_token' => Str::random(80),
            ]);

            Mail::to($admin->email)->send(new WelcomeAdminNotification($admin, $password));

            $this->sendNewRegistrationNotifications($admin);

            Log::info('ProcessExternalPaymentRegistrationJob: Admin created and notifications sent', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessExternalPaymentRegistrationJob failed', [
                'payment_id' => $this->paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            report($e);
        }
    }

    protected function sendNewRegistrationNotifications(object $admin): void
    {
        $config = CoreConfig::query()
            ->where('code', 'registration.notifications.new_registration_emails')
            ->whereNull('channel_code')
            ->whereNull('locale_code')
            ->first();

        $emailsRaw = $config ? (string) $config->value : '';
        if ($emailsRaw === '') {
            return;
        }

        $emails = array_map('trim', explode(',', $emailsRaw));
        $emails = array_filter($emails, static fn (string $e) => filter_var($e, FILTER_VALIDATE_EMAIL));
        $emails = array_unique($emails);

        foreach ($emails as $email) {
            try {
                Mail::to($email)->send(new NewRegistrationNotification($admin));
            } catch (\Throwable $e) {
                Log::error('Failed to send new registration notification', [
                    'recipient' => $email,
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
