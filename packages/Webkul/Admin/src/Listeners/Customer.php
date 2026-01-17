<?php

namespace Webkul\Admin\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webkul\Admin\Mail\Customer\RegistrationNotification;

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
        try {
            if (! core()->getConfigData('emails.general.notifications.emails.general.notifications.customer_registration_confirmation_mail_to_admin')) {
                Log::debug('Customer registration notification to admin is disabled', [
                    'customer_id' => $customer->id,
                    'customer_email' => $customer->email,
                ]);
                return;
            }

            $adminEmailDetails = core()->getAdminEmailDetails();
            
            Log::info('Sending customer registration notification to admin', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'customer_name' => $customer->first_name . ' ' . $customer->last_name,
                'recipient_email' => $adminEmailDetails['email'] ?? 'N/A',
                'recipient_name' => $adminEmailDetails['name'] ?? 'N/A',
            ]);

            Mail::queue(new RegistrationNotification($customer));

            Log::info('Customer registration notification to admin queued successfully', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'recipient_email' => $adminEmailDetails['email'] ?? 'N/A',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send customer registration notification to admin', [
                'customer_id' => $customer->id ?? null,
                'customer_email' => $customer->email ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            report($e);
        }
    }
}
