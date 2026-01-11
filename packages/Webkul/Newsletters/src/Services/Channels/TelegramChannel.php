<?php

namespace Webkul\Newsletters\Services\Channels;

use Webkul\Newsletters\Contracts\MailingChannelInterface;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\TelegramBotInstance;
use Webkul\Newsletters\Models\CustomerNumber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class TelegramChannel implements MailingChannelInterface
{
    private const TELEGRAM_API_URL = 'https://api.telegram.org/bot';

    public function getChannelType(): string
    {
        return 'telegram';
    }

    public function sendMessage(object $instance, CustomerNumber $customer, string $message): ?string
    {
        if (!$instance instanceof TelegramBotInstance) {
            Log::error('TelegramChannel: Invalid instance type', ['instance' => get_class($instance)]);
            return null;
        }

        $chatId = $this->getRecipientIdentifier($customer);
        if (!$chatId) {
            Log::error('TelegramChannel: Customer has no telegram_id', ['customer_id' => $customer->id]);
            return null;
        }

        try {
            Log::info('TelegramChannel: sending message', [
                'instance_id'  => $instance->id,
                'customer_id'  => $customer->id,
                'chat_id'      => $chatId,
                'mailing_list_id' => $customer->mailing_list_id ?? null,
                'message_preview' => mb_substr($message, 0, 200),
            ]);

            // Проверяем наличие медиа файлов в message_links
            $mailingList = $customer->mailingList;
            if ($mailingList && $mailingList->message_links && !empty($mailingList->message_links)) {
                $media = $mailingList->message_links[0];
                $fileUrl = $media['url'] ?? null;
                
                if ($fileUrl) {
                    // Если URL относительный, делаем его абсолютным
                    if (!preg_match('/^https?:\/\//', $fileUrl)) {
                        $fileUrl = url($fileUrl);
                    }
                    
                    $mediaType = $media['type'] ?? 'document';
                    $mimeType = $media['mime_type'] ?? '';
                    
                    // Определяем, является ли файл изображением
                    $isImage = $mediaType === 'image' || strpos($mimeType, 'image/') === 0;
                    
                    // Отправляем медиа файл с текстом сообщения как подписью
                    if ($isImage) {
                        $messageId = $this->sendPhoto($instance, $chatId, $fileUrl, $message);
                    } else {
                        $messageId = $this->sendDocument($instance, $chatId, $fileUrl, $message);
                    }
                    
                    if ($messageId) {
                        Log::info('Telegram media sent successfully', [
                            'instance_id' => $instance->id,
                            'customer_id' => $customer->id,
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'media_type' => $mediaType,
                            'media_url' => $fileUrl,
                        ]);
                        
                        return (string) $messageId;
                    } else {
                        Log::warning('Telegram media sending failed, falling back to text message', [
                            'instance_id' => $instance->id,
                            'customer_id' => $customer->id,
                            'chat_id' => $chatId,
                        ]);
                        // Продолжаем отправку текстового сообщения как fallback
                    }
                }
            }

            // Отправляем обычное текстовое сообщение
            $response = Http::post(self::TELEGRAM_API_URL . $instance->bot_token . '/sendMessage', [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['ok']) && $data['ok']) {
                $messageId = $data['result']['message_id'] ?? null;

                Log::info('Telegram message sent successfully', [
                    'instance_id' => $instance->id,
                    'customer_id' => $customer->id,
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ]);

                return (string) $messageId;
            }

            Log::error('Telegram API error', [
                'instance_id' => $instance->id,
                'customer_id' => $customer->id,
                'chat_id' => $chatId,
                'response' => $data,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Telegram sending failed', [
                'instance_id' => $instance->id,
                'customer_id' => $customer->id,
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function validateRecipient(CustomerNumber $customer): bool
    {
        $telegramId = $this->getRecipientIdentifier($customer);
        return !empty($telegramId);
    }

    public function getActiveInstances(MailingList $mailingList): Collection
    {
        return $mailingList->telegramInstances()->where('active', true)->get();
    }

    public function getRecipientIdentifier(CustomerNumber $customer): ?string
    {
        return $customer->telegram_id ?? null;
    }

    /**
     * Send photo with caption via Telegram.
     */
    public function sendPhoto(TelegramBotInstance $instance, string $chatId, string $photoUrl, ?string $caption = null): ?string
    {
        try {
            $response = Http::post(self::TELEGRAM_API_URL . $instance->bot_token . '/sendPhoto', [
                'chat_id' => $chatId,
                'photo' => $photoUrl,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['ok']) && $data['ok']) {
                return (string) ($data['result']['message_id'] ?? null);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Telegram photo sending failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send document via Telegram.
     */
    public function sendDocument(TelegramBotInstance $instance, string $chatId, string $documentUrl, ?string $caption = null): ?string
    {
        try {
            $response = Http::post(self::TELEGRAM_API_URL . $instance->bot_token . '/sendDocument', [
                'chat_id' => $chatId,
                'document' => $documentUrl,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['ok']) && $data['ok']) {
                return (string) ($data['result']['message_id'] ?? null);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Telegram document sending failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}



