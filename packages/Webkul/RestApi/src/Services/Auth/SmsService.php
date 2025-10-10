<?php

namespace Webkul\RestApi\Services\Auth;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS verification code.
     *
     * @param string $phoneNumber
     * @param string $code
     * @return bool
     */
    public function sendVerificationCode(string $phoneNumber, string $code): bool
    {
        try {
            // TODO: Integrate with actual SMS provider (Twilio, AWS SNS, etc.)
            // For now, we'll just log the code for development purposes
            Log::info("SMS Verification Code for {$phoneNumber}: {$code}");
            
            // Example integration with Twilio:
            // $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
            // $twilio->messages->create($phoneNumber, [
            //     'from' => config('services.twilio.from'),
            //     'body' => "Your verification code is: {$code}"
            // ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("SMS sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate phone number format.
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        return preg_match('/^\+?[1-9]\d{1,14}$/', $phoneNumber);
    }
}
