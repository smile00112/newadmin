<?php

namespace Webkul\Newsletters\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Newsletters\Exceptions\GreenApiRequestException;
use Webkul\Newsletters\Exceptions\GreenApiDataValidateException;

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
     * @throws GreenApiDataValidateException validate data error
     * @throws GreenApiRequestException request error
 */
    public function sendMessage(string $chatId, string $message, ?string $quotedMessageId = null, bool $linkPreview = true): array
    {

        if (empty($chatId) || empty($message)) {
            //throw new Exception('chatId и message являются обязательными параметрами.');
            throw new GreenApiDataValidateException("GreenAPIService sendMessage  chatId и message являются обязательными параметрами.");
        }

        if (strlen($message) > 20000) {
            throw new GreenApiDataValidateException("GreenAPIService sendMessage Длина текста сообщения не должна превышать 20000 символов.");
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
            throw new GreenApiRequestException('GreenAPIService sendMessage Ошибка API: ' . $response->body(), $response->status());
        }

        return $response->json();
    }

    /**
     * Получение истории сообщений чата
     *
     * @param string $chatId Идентификатор личного или группового чата:
     * @param int $count Количество сообщений для получения (по умолчанию 100, необязательный)
     * @return array
     * @throws GreenApiDataValidateException validate data error
     * @throws GreenApiRequestException request error
     */
    public function getChatHistory(string $chatId, int $count = 100): array
    {
        if (empty($chatId)) {
            throw new GreenApiDataValidateException('chatId является обязательным параметром.');
        }

        if ($count <= 0) {
            throw new GreenApiDataValidateException('count должен быть положительным числом.');
        }

        // Формирование тела запроса:cite[1]
        $payload = [
            'chatId' => $chatId,
            'count' => $count,
        ];

        // Формирование URL для запроса
        $endpoint = "{$this->apiUrl}/waInstance{$this->idInstance}/getChatHistory/{$this->apiTokenInstance}";

        // Отправка POST-запроса:cite[1]
        $response = Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, $payload);

        // Обработка ответа
        if (!$response->successful()) {
            throw new GreenApiRequestException('Ошибка API при получении истории чата: ' . $response->body(), $response->status());
        }

        return $response->json();

    }
}
