<?php
// Файл: includes/Service/ApiClient.php

declare(strict_types=1);

namespace RemoteOnlinePayments\Service;

use Exception;
use WC_Order;

/**
 * Клиент для отправки запросов к стороннему серверу.
 */
final class ApiClient
{
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
     * @param Settings $settings Объект с настройками шлюза
     * @param Logger $logger Экземпляр логгера
     */
    public function __construct(Settings $settings, Logger $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Отправляет запрос на создание платежа на сторонний сервер.
     *
     * @param WC_Order $order Объект заказа
     * @return string URL для оплаты
     * @throws Exception В случае ошибки при отправке запроса или обработке ответа
     */
    public function createPayment(WC_Order $order): string
    {
        $apiUrl = $this->settings->getApiUrl();
        
        if (empty($apiUrl)) {
            throw new Exception(__('URL API сервера не настроен.', 'remote-online-payments'));
        }

        $requestData = $this->buildRequestData($order);
        
        $this->logger->log(
            sprintf('Отправка запроса на создание платежа для заказа #%d', $order->get_id()),
            ApiConstants::LOG_LEVEL_INFO,
            $this->maskSensitiveData($requestData)
        );

        $response = $this->sendRequest($apiUrl, $requestData);
        
        $paymentUrl = $this->extractPaymentUrl($response);
        
        if (empty($paymentUrl)) {
            throw new Exception(__('Не удалось получить ссылку на оплату от сервера.', 'remote-online-payments'));
        }

        $this->logger->log(
            sprintf('Получена ссылка на оплату для заказа #%d', $order->get_id()),
            ApiConstants::LOG_LEVEL_INFO
        );

        return $paymentUrl;
    }

    /**
     * Формирует данные для запроса.
     *
     * @param WC_Order $order Объект заказа
     * @return array Данные запроса
     */
    private function buildRequestData(WC_Order $order): array
    {
        $customerName = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        if (empty($customerName)) {
            $customerName = $order->get_billing_company() ?: __('Гость', 'remote-online-payments');
        }

        $data = [
            'order_id' => $order->get_id(),
            'order_total' => (float) $order->get_total(),
            'customer_name' => $customerName,
            'customer_email' => $order->get_billing_email(),
            'customer_phone' => preg_replace('/[^0-9+]/', '', $order->get_billing_phone() ?? ''),
            'order_key' => $order->get_order_key(),
        ];

        // Добавляем API ключ, если он настроен
        $apiKey = $this->settings->getApiKey();
        if (!empty($apiKey)) {
            $data['api_key'] = $apiKey;
        }

        // Добавляем подпись, если секретный ключ настроен
        $secretKey = $this->settings->getSecretKey();
        if (!empty($secretKey)) {
            $data['signature'] = $this->calculateSignature($data, $secretKey);
        }

        /**
         * Фильтр позволяет модифицировать данные запроса перед отправкой.
         *
         * @param array $data Данные запроса
         * @param WC_Order $order Объект заказа
         */
        return apply_filters('remote_online_payments_request_data', $data, $order);
    }

    /**
     * Вычисляет подпись запроса.
     *
     * @param array $data Данные запроса
     * @param string $secretKey Секретный ключ
     * @return string Подпись
     */
    private function calculateSignature(array $data, string $secretKey): string
    {
        // Исключаем signature из данных для расчета подписи
        $dataForSign = $data;
        unset($dataForSign['signature']);
        
        // Сортируем массив по ключам для консистентности
        ksort($dataForSign);
        
        // Формируем строку для подписи
        $stringToSign = '';
        foreach ($dataForSign as $key => $value) {
            $stringToSign .= $key . '=' . $value . '&';
        }
        $stringToSign .= 'secret=' . $secretKey;
        
        return hash('sha256', $stringToSign);
    }

    /**
     * Отправляет HTTP запрос на сервер.
     *
     * @param string $url URL API
     * @param array $data Данные запроса
     * @return array Ответ сервера
     * @throws Exception В случае ошибки при отправке запроса
     */
    private function sendRequest(string $url, array $data): array
    {
        $args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ];

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $errorMessage = sprintf(
                __('Ошибка при отправке запроса на сервер: %s', 'remote-online-payments'),
                $response->get_error_message()
            );
            $this->logger->log($errorMessage, ApiConstants::LOG_LEVEL_ERROR);
            throw new Exception($errorMessage);
        }

        $statusCode = wp_remote_retrieve_response_code($response);
        if ($statusCode !== 200) {
            $errorMessage = sprintf(
                __('Сервер вернул ошибку: HTTP %d', 'remote-online-payments'),
                $statusCode
            );
            $this->logger->log($errorMessage, ApiConstants::LOG_LEVEL_ERROR);
            throw new Exception($errorMessage);
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = sprintf(
                __('Ошибка декодирования JSON ответа: %s', 'remote-online-payments'),
                json_last_error_msg()
            );
            $this->logger->log($errorMessage, ApiConstants::LOG_LEVEL_ERROR);
            throw new Exception($errorMessage);
        }

        return $decoded;
    }

    /**
     * Извлекает URL оплаты из ответа сервера.
     *
     * @param array $response Ответ сервера
     * @return string URL оплаты или пустая строка
     */
    private function extractPaymentUrl(array $response): string
    {
        // Проверяем различные возможные поля для URL оплаты
        $possibleKeys = ['payment_url', 'url', 'paymentUrl', 'redirect_url', 'redirectUrl', 'link'];
        
        foreach ($possibleKeys as $key) {
            if (isset($response[$key]) && is_string($response[$key]) && !empty($response[$key])) {
                return esc_url_raw($response[$key]);
            }
        }

        return '';
    }

    /**
     * Маскирует чувствительные данные в массиве.
     *
     * @param array $data Данные для маскировки
     * @return array Данные с замаскированными значениями
     */
    private function maskSensitiveData(array $data): array
    {
        $sensitiveKeys = ['api_key', 'secret_key', 'signature'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '****';
            }
        }

        return $data;
    }
}
