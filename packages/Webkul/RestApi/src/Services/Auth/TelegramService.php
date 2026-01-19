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
     */
    public function sendVerificationCode(string $telegramId, string $code, ?string $channelCode = null): bool
    {
        try {
            $channelCode = $channelCode ?? core()->getCurrentChannelCode();

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

            $botToken = $this->getBotToken($channelCode);

            if (!$botToken) {
                Log::error("Telegram bot token is not configured.");
                return false;
            }

            // Prepare message
            $message = "Ваш код подтверждения: {$code}";

            // Send message via Telegram Bot API
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $telegramId,
                'text'    => $message,
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
     */
    public function validateTelegramId(string $telegramId): bool
    {
        return is_numeric($telegramId) && strlen($telegramId) >= 8;
    }

    /**
     * Get bot token from settings.
     */
    public function getBotToken(?string $channelCode = null): ?string
    {
        $channelCode = $channelCode ?? core()->getCurrentChannelCode();

        return $this->settingRepository->getSetting('telegram', 'bot_token', $channelCode)
            ?? config('services.telegram.bot_token');
    }

    /**
     * Get bot URL from settings.
     */
    public function getBotUrl(?string $channelCode = null): ?string
    {
        $channelCode = $channelCode ?? core()->getCurrentChannelCode();

        return $this->settingRepository->getSetting('telegram', 'bot_link', $channelCode);
    }

    /**
     * Send message with contact request keyboard.
     */
    public function sendContactRequestKeyboard(string $telegramId, ?string $channelCode = null): bool
    {
        try {
            $channelCode = $channelCode ?? core()->getCurrentChannelCode();
            $botToken = $this->getBotToken($channelCode);

            if (!$botToken) {
                Log::error("Telegram bot token is not configured.");
                return false;
            }

            $message = $this->settingRepository->getSetting('telegram', 'start_message', $channelCode)
                ?? "Добро пожаловать! Для авторизации, пожалуйста, поделитесь своим контактом, нажав на кнопку ниже.";

            $keyboard = [
                'keyboard' => [
                    [
                        [
                            'text'            => '📱 Поделиться контактом',
                            'request_contact' => true,
                        ],
                    ],
                ],
                'resize_keyboard'   => true,
                'one_time_keyboard' => true,
            ];

            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id'      => $telegramId,
                'text'         => $message,
                'reply_markup' => json_encode($keyboard),
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                if (isset($responseData['ok']) && $responseData['ok']) {
                    Log::info("Contact request keyboard sent to {$telegramId}");
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
            Log::error("Telegram contact request failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register webhook URL in Telegram.
     */
    public function registerWebhook(string $webhookUrl, ?string $channelCode = null): array
    {
        try {
            $channelCode = $channelCode ?? core()->getCurrentChannelCode();
            $botToken = $this->getBotToken($channelCode);

            if (!$botToken) {
                return [
                    'success' => false,
                    'error'   => 'Bot token not configured',
                ];
            }

            $response = Http::post("https://api.telegram.org/bot{$botToken}/setWebhook", [
                'url'             => $webhookUrl,
                'allowed_updates' => ['message'],
            ]);

            $data = $response->json();

            if ($data['ok'] ?? false) {
                Log::info("Telegram webhook registered successfully: {$webhookUrl}");

                return [
                    'success' => true,
                    'url'     => $webhookUrl,
                ];
            }

            Log::error("Telegram webhook registration failed: " . json_encode($data));

            return [
                'success' => false,
                'error'   => $data['description'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            Log::error("Telegram webhook registration exception: " . $e->getMessage());

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Get current webhook info from Telegram.
     */
    public function getWebhookInfo(?string $channelCode = null): array
    {
        try {
            $channelCode = $channelCode ?? core()->getCurrentChannelCode();
            $botToken = $this->getBotToken($channelCode);

            if (!$botToken) {
                return [
                    'success' => false,
                    'error'   => 'Bot token not configured',
                ];
            }

            $response = Http::get("https://api.telegram.org/bot{$botToken}/getWebhookInfo");

            $data = $response->json();

            if ($data['ok'] ?? false) {
                return [
                    'success' => true,
                    'result'  => $data['result'],
                ];
            }

            return [
                'success' => false,
                'error'   => $data['description'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete webhook from Telegram.
     */
    public function deleteWebhook(?string $channelCode = null): array
    {
        try {
            $channelCode = $channelCode ?? core()->getCurrentChannelCode();
            $botToken = $this->getBotToken($channelCode);

            if (!$botToken) {
                return [
                    'success' => false,
                    'error'   => 'Bot token not configured',
                ];
            }

            $response = Http::post("https://api.telegram.org/bot{$botToken}/deleteWebhook");

            $data = $response->json();

            if ($data['ok'] ?? false) {
                Log::info("Telegram webhook deleted successfully");

                return ['success' => true];
            }

            return [
                'success' => false,
                'error'   => $data['description'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a simple message to user.
     */
    public function sendMessage(string $telegramId, string $message, ?string $channelCode = null): bool
    {
        try {
            $channelCode = $channelCode ?? core()->getCurrentChannelCode();
            $botToken = $this->getBotToken($channelCode);

            if (!$botToken) {
                Log::error("Telegram bot token is not configured.");
                return false;
            }

            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $telegramId,
                'text'    => $message,
            ]);

            return $response->successful() && ($response->json()['ok'] ?? false);
        } catch (\Exception $e) {
            Log::error("Telegram message send failed: " . $e->getMessage());
            return false;
        }
    }
}
