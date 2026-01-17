<?php

namespace Webkul\Shop\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webkul\Shop\Mail\Customer\EmailVerificationNotification;
use Webkul\Shop\Mail\Customer\NoteNotification;
use Webkul\Shop\Mail\Customer\RegistrationNotification;
use Webkul\Shop\Mail\Customer\SubscriptionNotification;
use Webkul\Shop\Mail\Customer\UpdatePasswordNotification;

class Customer extends Base
{
    /**
     * After customer is created
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @return void
     */
    public function afterCreated($customer)
    {
        if (core()->getConfigData('customer.settings.email.verification')) {
            try {
                if (! core()->getConfigData('customer.settings.email.verification')) {
                    Log::debug('Email verification notification is disabled', [
                        'customer_id' => $customer->id,
                        'customer_email' => $customer->email,
                    ]);
                    return;
                }

                Log::info('Sending email verification notification', [
                    'customer_id' => $customer->id,
                    'customer_email' => $customer->email,
                ]);

                Mail::queue(new EmailVerificationNotification($customer));

                Log::info('Email verification notification queued successfully', [
                    'customer_id' => $customer->id,
                    'customer_email' => $customer->email,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send email verification notification', [
                    'customer_id' => $customer->id ?? null,
                    'customer_email' => $customer->email ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                report($e);
            }

            return;
        }

        try {
            if (! core()->getConfigData('emails.general.notifications.emails.general.notifications.registration')) {
                Log::debug('Customer registration notification is disabled', [
                    'customer_id' => $customer->id,
                    'customer_email' => $customer->email,
                ]);
                return;
            }

            Log::info('Sending customer registration notification', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'customer_name' => $customer->first_name . ' ' . $customer->last_name,
            ]);

            Mail::queue(new RegistrationNotification($customer));

            Log::info('Customer registration notification queued successfully', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send customer registration notification', [
                'customer_id' => $customer->id ?? null,
                'customer_email' => $customer->email ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            report($e);
        }
    }

    /**
     * Send mail on updating password.
     *
     * @param  \Webkul\Customer\Models\Customer  $customer
     * @return void
     */
    public function afterPasswordUpdated($customer)
    {
        try {
            Mail::queue(new UpdatePasswordNotification($customer));
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Send mail on subscribe
     *
     * @param  \Webkul\Customer\Models\Customer  $customer
     * @return void
     */
    public function afterSubscribed($customer)
    {
        try {
            Mail::queue(new SubscriptionNotification($customer));
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Send mail on creating Note
     *
     * @param  \Webkul\Customer\Models\Customer  $customer
     * @return void
     */
    public function afterNoteCreated($note)
    {
        if (! $note->customer_notified) {
            return;
        }

        try {
            Mail::queue(new NoteNotification($note));
        } catch (\Exception $e) {
            session()->flash('warning', $e->getMessage());
        }
    }
}
