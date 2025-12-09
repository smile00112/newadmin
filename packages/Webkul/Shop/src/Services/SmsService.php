<?php

namespace Webkul\Shop\Services;

use Webkul\Shop\Models\PhoneVerificationCode;
use Webkul\Shop\Services\InMemoryVerificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SmsService
{
    /**
     * Send verification code to the given phone number.
     *
     * @param string $phone
     * @return array
     */
    public function sendVerificationCode($phone)
    {
        try {
            // Check if database table exists, otherwise use in-memory storage
            if (Schema::hasTable('phone_verification_codes')) {
                // Use database storage
                $verificationCode = PhoneVerificationCode::generateCode($phone);
                $code = $verificationCode->code;
            } else {
                // Use in-memory storage as fallback
                $verificationData = InMemoryVerificationService::generateCode($phone);
                $code = $verificationData['code'];
            }
            
            // In a real implementation, you would send SMS here
            // For now, we'll just log it for testing
            Log::info("SMS Code for {$phone}: {$code}");
            
            // TODO: Implement actual SMS sending logic
            // Example with a hypothetical SMS provider:
            /*
            $smsProvider = new YourSmsProvider();
            $message = "Your verification code is: {$code}";
            $smsProvider->send($phone, $message);
            */
            
            return [
                'success' => true,
                'message' => 'Verification code sent successfully',
                'expires_in' => 300 // 5 minutes in seconds
            ];
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to send verification code'
            ];
        }
    }

    /**
     * Send SMS using external provider (implement based on your SMS provider).
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    private function sendSms($phone, $message)
    {
        // Implement your SMS provider logic here
        // Examples:
        
        // For Twilio:
        // $client = new \Twilio\Rest\Client(config('sms.twilio.sid'), config('sms.twilio.token'));
        // $client->messages->create($phone, ['from' => config('sms.twilio.from'), 'body' => $message]);
        
        // For other providers, implement accordingly
        
        return true; // Return true if SMS was sent successfully
    }
}
