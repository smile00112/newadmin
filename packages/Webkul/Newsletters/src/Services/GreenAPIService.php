<?php

namespace Webkul\Newsletters\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GreenAPIService
{
    private string $apiUrl;
    private string $idInstance;
    private string $apiTokenInstance;

    public function __construct($apiUrl, $idInstance, $apiTokenInstance)
    {
        $this->idInstance = $idInstance;
        $this->apiTokenInstance = $apiTokenInstance;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Отправка текстового сообщения в личный или групповой чат
     *
     * @param string $chatId ID чата (например, "79876543210@c.us" для личного чата)
     * @param string $message Текст сообщения (макс. 20000 символов)
     * @param string|null $quotedMessageId ID сообщения для ответа (опционально)
     * @param bool $linkPreview Включить превью для ссылок (по умолчанию true)
     * @return array
     * @throws Exception
     */
    public function sendMessage(string $chatId, string $message, ?string $quotedMessageId = null, bool $linkPreview = true): array
    {
        // Базовая валидация
        if (empty($chatId) || empty($message)) {
            //throw new Exception('chatId и message являются обязательными параметрами.');
            Log::error("GreenAPIService sendMessage  chatId и message являются обязательными параметрами.");
        }

        if (strlen($message) > 20000) {
            //throw new Exception('Длина текста сообщения не должна превышать 20000 символов.');
            Log::error("GreenAPIService sendMessage Длина текста сообщения не должна превышать 20000 символов.");

        }

        // Формирование тела запроса
        $payload = [
            'chatId' => $chatId,
            'message' => $message,
            'linkPreview' => $linkPreview,
        ];

        if (!empty($quotedMessageId)) {
            $payload['quotedMessageId'] = $quotedMessageId;
        }

        // Формирование URL для запроса
        $endpoint = "{$this->apiUrl}/waInstance{$this->idInstance}/sendMessage/{$this->apiTokenInstance}";

        // Отправка POST-запроса
        $response = Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, $payload);

        // Обработка ответа
        if (!$response->successful()) {
            //throw new Exception('GreenAPIService sendMessage Ошибка API: ' . $response->body(), $response->status());
            Log::error("GreenAPIService sendMessage Ошибка API:", [
                'body' =>  $response->body(),
                'status' => $response->status(),
            ]);
            return [];
        }

        return $response->json();
    }
}
