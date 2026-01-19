<?php

namespace Webkul\RestApi\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\RestApi\Repositories\AuthChannelSettingRepository;

class TelegramService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected AuthChannelSettingRepository $settingRepository,
        protected TestUserService $testUserService
    ) {}

    /**
     * Send Telegram verification code.
     *
     * @param string $telegramId
     * @param string $code
     * @return bool
     */
    public function sendVerificationCode(string $telegramId, string $code): bool
    {
        try {
            $channelCode = core()->getCurrentChannelCode();

            // Check if this is a test Telegram ID - skip sending message
            if ($this->testUserService->isTestUser($telegramId, 'telegram')) {
                Log::info("Telegram message sending skipped for test Telegram ID: {$telegramId}");
                return true;
            }
            
            // Check if Telegram channel is enabled
            if (!$this->settingRepository->isChannelEnabled('telegram', $channelCode)) {
                Log::warning("Telegram channel is disabled for channel: {$channelCode}");
                return false;
            }

            // Get settings from database with fallback to config
            $botToken = $this->settingRepository->getSetting('telegram', 'bot_token', $channelCode)
                ?? config('services.telegram.bot_token');

            if (!$botToken) {
                Log::error("Telegram bot token is not configured.");
                return false;
            }

            // Prepare message
            $message = "Ваш код подтверждения: {$code}";

            // Send message via Telegram Bot API
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $telegramId,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if (isset($responseData['ok']) && $responseData['ok']) {
                    Log::info("Telegram message sent successfully to {$telegramId}");
                    return true;
                } else {
                    Log::error("Telegram API returned error: " . json_encode($responseData));
                    return false;
                }
            } else {
                Log::error("Telegram API request failed with status: " . $response->status());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Telegram sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate Telegram ID format.
     *
     * @param string $telegramId
     * @return bool
     */
    public function validateTelegramId(string $telegramId): bool
    {
        return is_numeric($telegramId) && strlen($telegramId) >= 8;
    }
}
