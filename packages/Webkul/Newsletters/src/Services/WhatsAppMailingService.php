<?php

namespace Webkul\Newsletters\Services;

use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Models\CustomerNumber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Webkul\Newsletters\Services\GreenAPIService;

class WhatsAppMailingService
{
    /**
     * Send WhatsApp message using VacapInstance
     */
    public function sendMessage(VacapInstance $instance, string $phoneNumber, string $message): string | null
    {
        try {
            $greenApiService = new GreenAPIService($instance->link_name, $instance->login, $instance->password);
            //'chatId' => $phone.'@c.us',
            $response = $greenApiService->sendMessage($phoneNumber.'@c.us', $message);

            if ($response['idMessage']) {
                Log::info("WhatsApp message sent successfully", [
                    'instance_id' => $instance->id,
                    'phone' => $phoneNumber,
                    'response' => $response
                ]);

                return $response['idMessage'];
            }

            Log::error("WhatsApp API error", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'response' => $response
            ]);

            return null;
        } catch (\Exception $e) {
            echo $e->getMessage();
            Log::error("WhatsApp sending failed", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get chat history and parse messages for display
     */
    public function getChatHistory(VacapInstance $instance, string $phoneNumber, int $count = 50): string
    {
        try {
            $greenApiService = new GreenAPIService($instance->link_name, $instance->login, $instance->password);
            $response = $greenApiService->getChatHistory($phoneNumber.'@c.us', $count);

            if (isset($response) && is_array($response)) {
                Log::info("WhatsApp chat history retrieved successfully", [
                    'instance_id' => $instance->id,
                    'phone' => $phoneNumber,
                    'messages_count' => count($response)
                ]);

                return $this->parseChatHistory($response);
            }

            Log::error("WhatsApp API error - no messages found", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'response' => $response
            ]);

            return "История сообщений не найдена.";
        } catch (\Exception $e) {
            Log::error("WhatsApp chat history failed", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return "Ошибка при получении истории сообщений: " . $e->getMessage();
        }
    }

    /**
     * Parse chat history messages for display in textarea
     */
    private function parseChatHistory(array $messages): string
    {
        if (empty($messages)) {
            return "Сообщения не найдены.";
        }

        $parsedMessages = [];

        foreach ($messages as $message) {
            $parsedMessage = $this->parseMessage($message);
            if ($parsedMessage) {
                $parsedMessages[] = $parsedMessage;
            }
        }

        return implode("\n", $parsedMessages);
    }

    /**
     * Parse individual message based on type
     */
    private function parseMessage(array $message): ?string
    {
        $timestamp = isset($message['timestamp']) ? date('d.m.Y H:i:s', $message['timestamp']) : 'Неизвестно';
        $type = $message['type'] ?? 'unknown';
        $typeMessage = $message['typeMessage'] ?? 'unknown';
        $senderName = $message['senderName'] ?? 'Неизвестно';

        // Определяем направление сообщения
        $direction = $type === 'incoming' ? 'Входящее' : 'Исходящее';

        // Формируем базовую информацию о сообщении
        $messageInfo = "[{$timestamp}] {$direction} от {$senderName}";

        // Обрабатываем разные типы сообщений
        switch ($typeMessage) {
            case 'textMessage':
            case 'extendedTextMessage':
                $text = $message['textMessage'] ?? 'Текстовое сообщение';
                return "{$messageInfo}: {$text}";

            case 'imageMessage':
                $caption = $message['caption'] ?? '';
                return "{$messageInfo}: [Изображение]" . ($caption ? " - {$caption}" : '');

            case 'videoMessage':
                $caption = $message['caption'] ?? '';
                return "{$messageInfo}: [Видео]" . ($caption ? " - {$caption}" : '');

            case 'audioMessage':
                return "{$messageInfo}: [Аудио сообщение]";

            case 'documentMessage':
                $fileName = $message['fileName'] ?? 'Документ';
                return "{$messageInfo}: [Документ: {$fileName}]";

            case 'locationMessage':
                $latitude = $message['latitude'] ?? '';
                $longitude = $message['longitude'] ?? '';
                $name = $message['name'] ?? '';
                return "{$messageInfo}: [Геолокация: {$name}] ({$latitude}, {$longitude})";

            case 'contactMessage':
                $name = $message['name'] ?? '';
                $phone = $message['phone'] ?? '';
                return "{$messageInfo}: [Контакт: {$name} - {$phone}]";

            case 'stickerMessage':
                return "{$messageInfo}: [Стикер]";

            case 'reactionMessage':
                $reactionText = $message['reactionText'] ?? '';
                return "{$messageInfo}: [Реакция: {$reactionText}]";

            case 'pollMessage':
                $pollName = $message['pollName'] ?? '';
                return "{$messageInfo}: [Опрос: {$pollName}]";

            case 'deletedMessage':
                return "{$messageInfo}: [Сообщение удалено]";

            case 'systemMessage':
                $text = $message['textMessage'] ?? 'Системное сообщение';
                return "{$messageInfo}: [Система] {$text}";

            default:
                // Для неизвестных типов сообщений показываем базовую информацию
                $text = $message['textMessage'] ?? $message['caption'] ?? 'Неизвестный тип сообщения';
                return "{$messageInfo}: [{$typeMessage}] {$text}";
        }
    }



    /**
     * Get random VacapInstance from mailing list
     */
    public function getRandomInstance(MailingList $mailingList): ?VacapInstance
    {
        $i = $mailingList->whatsappInstances()
            ->inRandomOrder();

        Log::warning("getRandomInstance", [
            'random_instances' => $i,

        ]);

        return $i->first();
    }

    /**
     * Check rate limit (40 messages per second)
     */
    public function checkRateLimit(): bool
    {
        $key = 'whatsapp_rate_limit:' . now()->format('Y-m-d-H-i-s');
        $current = Redis::get($key) ?? 0;

        if ($current >= 40) {
            return false;
        }

        Redis::incr($key);
        Redis::expire($key, 1); // Expire after 1 second

        return true;
    }

    public function makeRandomMessage($text): string
    {
        return $result = preg_replace_callback('/\{([^}]+)\}/', function($matches) {
            $options = explode('|', $matches[1]);
            return $options[array_rand($options)];
        }, $text);
    }

    public function makeRandomInstance($instances)
    {
        return $instances->random();
    }

    /**
     * Send WhatsApp media file by URL using VacapInstance
     * 
     * @param VacapInstance $instance
     * @param string $phoneNumber
     * @param string $urlFile URL ссылка на файл
     * @param string $fileName Имя файла с расширением
     * @param string|null $caption Подпись к файлу (опционально)
     * @return string|null ID сообщения или null при ошибке
     */
    public function sendFileByUrl(
        VacapInstance $instance,
        string $phoneNumber,
        string $urlFile,
        string $fileName,
        ?string $caption = null
    ): ?string {
        try {
            $greenApiService = new GreenAPIService($instance->link_name, $instance->login, $instance->password);
            $response = $greenApiService->sendFileByUrl(
                $phoneNumber . '@c.us',
                $urlFile,
                $fileName,
                $caption
            );

            if (isset($response['idMessage'])) {
                Log::info("WhatsApp media file sent successfully", [
                    'instance_id' => $instance->id,
                    'phone' => $phoneNumber,
                    'url_file' => $urlFile,
                    'file_name' => $fileName,
                    'response' => $response
                ]);

                return $response['idMessage'];
            }

            Log::error("WhatsApp API error - no idMessage in response", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'url_file' => $urlFile,
                'response' => $response
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("WhatsApp media sending failed", [
                'instance_id' => $instance->id,
                'phone' => $phoneNumber,
                'url_file' => $urlFile,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
