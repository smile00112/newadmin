<?php
// Файл: includes/Service/CallbackHandler.php

declare(strict_types=1);

namespace RemoteOnlinePayments\Service;

use RemoteOnlinePayments\Exception\CallbackException;

/**
 * Обрабатывает и валидирует входящий callback-запрос от стороннего сервера.
 */
final class CallbackHandler
{
    /**
     * @var array<string, mixed> Данные из тела запроса
     */
    private array $requestData;

    /**
     * @var Settings DTO с настройками шлюза
     */
    private Settings $settings;

    /**
     * @var Logger Экземпляр логгера
     */
    private Logger $logger;

    /**
     * Конструктор.
     *
     * @param array<string, mixed> $requestData Данные из тела запроса
     * @param Settings $settings Объект с настройками шлюза
     * @param Logger $logger Экземпляр логгера
     */
    public function __construct(array $requestData, Settings $settings, Logger $logger)
    {
        // Убираем экранирование, добавленное PHP/WP
        $this->requestData = stripslashes_deep($requestData);
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Выполняет полную проверку входящего запроса.
     *
     * @return array{order_id: int, status: string, transaction_id: string|null, amount: float|null, message: string|null}
     * @throws CallbackException Если запрос невалиден
     */
    public function process(): array
    {
        $this->logger->log(
            'Получен входящий callback-запрос',
            ApiConstants::LOG_LEVEL_INFO,
            $this->maskSensitiveData($this->requestData)
        );

        // Проверка HTTP-метода
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? strtoupper(wp_unslash($_SERVER['REQUEST_METHOD'])) : 'GET';
        if ('POST' !== $requestMethod) {
            throw new CallbackException(__('Недопустимый метод HTTP-запроса. Ожидается POST.', 'remote-online-payments'), 405);
        }

        // Проверка наличия обязательных полей
        if (empty($this->requestData[ApiConstants::CB_PARAM_ORDER_ID])) {
            throw new CallbackException(__('Отсутствует обязательный параметр: order_id', 'remote-online-payments'), 400);
        }

        if (empty($this->requestData[ApiConstants::CB_PARAM_STATUS])) {
            throw new CallbackException(__('Отсутствует обязательный параметр: status', 'remote-online-payments'), 400);
        }

        // Валидация подписи, если секретный ключ настроен
        $secretKey = $this->settings->getSecretKey();
        if (!empty($secretKey) && !$this->validateSignature($secretKey)) {
            throw new CallbackException(__('Неверная подпись callback-запроса', 'remote-online-payments'), 403);
        }

        // Извлечение и валидация ID заказа
        $orderId = absint($this->requestData[ApiConstants::CB_PARAM_ORDER_ID]);
        if ($orderId <= 0) {
            throw new CallbackException(
                sprintf(__('Неверный формат order_id в callback: %s', 'remote-online-payments'), $this->requestData[ApiConstants::CB_PARAM_ORDER_ID]),
                400
            );
        }

        return [
            'order_id' => $orderId,
            'status' => sanitize_text_field($this->requestData[ApiConstants::CB_PARAM_STATUS]),
            'transaction_id' => isset($this->requestData[ApiConstants::CB_PARAM_TRANSACTION_ID]) 
                ? sanitize_text_field($this->requestData[ApiConstants::CB_PARAM_TRANSACTION_ID]) 
                : null,
            'amount' => isset($this->requestData[ApiConstants::CB_PARAM_AMOUNT]) 
                ? (float) $this->requestData[ApiConstants::CB_PARAM_AMOUNT] 
                : null,
            'message' => isset($this->requestData[ApiConstants::CB_PARAM_MESSAGE]) 
                ? sanitize_text_field($this->requestData[ApiConstants::CB_PARAM_MESSAGE]) 
                : null,
        ];
    }

    /**
     * Проверяет подпись запроса.
     *
     * @param string $secretKey Секретный ключ
     * @return bool True, если подпись верна, иначе false
     */
    private function validateSignature(string $secretKey): bool
    {
        if (empty($this->requestData['signature'])) {
            return false;
        }

        $receivedSignature = $this->requestData['signature'];
        
        // Формируем данные для проверки подписи (исключаем саму подпись)
        $dataForSign = $this->requestData;
        unset($dataForSign['signature']);
        
        // Сортируем массив по ключам
        ksort($dataForSign);
        
        // Формируем строку для подписи
        $stringToSign = '';
        foreach ($dataForSign as $key => $value) {
            $stringToSign .= $key . '=' . $value . '&';
        }
        $stringToSign .= 'secret=' . $secretKey;
        
        $expectedSignature = hash('sha256', $stringToSign);

        return hash_equals($expectedSignature, $receivedSignature);
    }

    /**
     * Генерирует ответ для отправки серверу после успешной обработки callback.
     *
     * @return string JSON ответ
     */
    public function getSuccessResponse(): string
    {
        return wp_json_encode([
            'status' => 'ok',
            'message' => __('Callback успешно обработан', 'remote-online-payments'),
        ]);
    }

    /**
     * Маскирует чувствительные данные в массиве для безопасного логирования.
     *
     * @param array<string, mixed> $data Исходный массив
     * @return array<string, mixed> Массив с замаскированными значениями
     */
    private function maskSensitiveData(array $data): array
    {
        $keysToMask = ['signature', 'secret_key', 'api_key'];
        foreach ($keysToMask as $key) {
            if (isset($data[$key])) {
                $data[$key] = '****';
            }
        }
        return $data;
    }
}
