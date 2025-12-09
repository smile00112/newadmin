<?php

namespace Webkul\RestApi\Services\Auth;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send WhatsApp verification code.
     *
     * @param string $phoneNumber
     * @param string $code
     * @return bool
     */
    public function sendVerificationCode(string $phoneNumber, string $code): bool
    {
        try {
            // TODO: Integrate with actual WhatsApp Business API
            // For now, we'll just log the code for development purposes
            Log::info("WhatsApp Verification Code for {$phoneNumber}: {$code}");
            
            // Example integration with WhatsApp Business API:
            // $whatsappApi = new WhatsAppApi(config('services.whatsapp.token'));
            // $whatsappApi->sendMessage($phoneNumber, "Your verification code is: {$code}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("WhatsApp sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate WhatsApp phone number format.
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        return preg_match('/^\+?[1-9]\d{1,14}$/', $phoneNumber);
    }
}
