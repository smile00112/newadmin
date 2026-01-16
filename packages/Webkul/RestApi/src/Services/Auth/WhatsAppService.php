<?php

namespace Webkul\RestApi\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\RestApi\Repositories\AuthChannelSettingRepository;

class WhatsAppService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected AuthChannelSettingRepository $settingRepository
    ) {}

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
            $channelCode = core()->getCurrentChannelCode();
            
            // Check if WhatsApp channel is enabled
            if (!$this->settingRepository->isChannelEnabled('whatsapp', $channelCode)) {
                Log::warning("WhatsApp channel is disabled for channel: {$channelCode}");
                return false;
            }

            // Get settings from database with fallback to config
            $idInstance = $this->settingRepository->getSetting('whatsapp', 'id_instance', $channelCode)
                ?? config('services.whatsapp.id_instance');
            $apiTokenInstance = $this->settingRepository->getSetting('whatsapp', 'api_token_instance', $channelCode)
                ?? config('services.whatsapp.api_token_instance');
            $url = $this->settingRepository->getSetting('whatsapp', 'url', $channelCode)
                ?? config('services.whatsapp.url', 'https://api.green-api.com');

            if (!$idInstance || !$apiTokenInstance) {
                Log::error("WhatsApp settings are incomplete. Instance ID and API token are required.");
                return false;
            }

            // Prepare message
            $message = "Ваш код подтверждения: {$code}";
            
            // Format phone number (remove + if present, Green API expects format without +)
            $formattedPhone = str_replace('+', '', $phoneNumber);

            // Send message via Green API
            $apiUrl = rtrim($url, '/') . "/waInstance{$idInstance}/sendMessage/{$apiTokenInstance}";
            
            $response = Http::post($apiUrl, [
                'chatId' => $formattedPhone . '@c.us',
                'message' => $message,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Green API returns idMessage on success
                if (isset($responseData['idMessage'])) {
                    Log::info("WhatsApp message sent successfully to {$phoneNumber}");
                    return true;
                } else {
                    Log::error("Green API returned error: " . json_encode($responseData));
                    return false;
                }
            } else {
                Log::error("Green API request failed with status: " . $response->status() . " - " . $response->body());
                return false;
            }
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
