<?php

namespace Webkul\RestApi\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\RestApi\Repositories\AuthChannelSettingRepository;

class SmsService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected AuthChannelSettingRepository $settingRepository
    ) {}

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
            $channelCode = core()->getCurrentChannelCode();
            
            // Check if SMS channel is enabled
            if (!$this->settingRepository->isChannelEnabled('sms', $channelCode)) {
                Log::warning("SMS channel is disabled for channel: {$channelCode}");
                return false;
            }

            // Get settings from database with fallback to config
            $login = $this->settingRepository->getSetting('sms', 'login', $channelCode)
                ?? config('services.redsms.login');
            $apiKey = $this->settingRepository->getSetting('sms', 'api_key', $channelCode)
                ?? config('services.redsms.api_key');
            $from = $this->settingRepository->getSetting('sms', 'from', $channelCode)
                ?? config('services.redsms.from');

            if (!$login || !$apiKey || !$from) {
                Log::error("SMS settings are incomplete. Login, API key, and sender name are required.");
                return false;
            }

            // Generate authentication
            $ts = 'ts-value-' . time();
            $secret = md5($ts . $apiKey);

            // Prepare message
            $message = "Ваш код подтверждения: {$code}";

            // Send SMS via REDSMS API
            $response = Http::withHeaders([
                'login' => $login,
                'ts' => $ts,
                'secret' => $secret,
                'Content-type' => 'application/json',
            ])->post('https://cp.redsms.ru/api/message', [
                'route' => 'sms',
                'from' => $from,
                'to' => $phoneNumber,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if (isset($responseData['success']) && $responseData['success']) {
                    Log::info("SMS sent successfully to {$phoneNumber}");
                    return true;
                } else {
                    Log::error("SMS API returned error: " . json_encode($responseData));
                    return false;
                }
            } else {
                Log::error("SMS API request failed with status: " . $response->status());
                return false;
            }
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
