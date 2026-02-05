<?php
// Файл: includes/Service/CallbackHandler.php

declare(strict_types=1);

namespace Tochka\Woocommerce\Service;

use Tochka\Woocommerce\Exception\CallbackException;

/**
 * Обрабатывает и валидирует входящий callback-запрос от банка.
 */
final class CallbackHandler
{
    /**
     * @var array<string, mixed> Санитизированные данные, полученные из $_POST.
     */
    private array $postData;

    /**
     * @var Settings DTO с настройками шлюза.
     */
    private Settings $settings;

    /**
     * @var Logger Экземпляр логгера.
     */
    private Logger $logger;

    /**
     * Конструктор.
     *
     * @param array<string, mixed>    $postData Необработанные данные из $_POST.
     * @param Settings                $settings Объект с настройками шлюза.
     * @param Logger                  $logger   Экземпляр логгера.
     */
    public function __construct(array $postData, Settings $settings, Logger $logger)
    {
        // WordPress функция stripslashes_deep убирает экранирование, добавленное PHP/WP.
        $this->postData = stripslashes_deep($postData);
        $this->settings = $settings;
        $this->logger   = $logger;
    }

    /**
     * Выполняет полную проверку входящего запроса.
     *
     * @return array{order_id: int, transaction_id: string, amount: float} Массив с валидированными данными.
     *
     * @throws CallbackException Если метод запроса не POST, отсутствуют параметры или неверная подпись.
     */
    public function process(): array
    {
        $this->logger->log(
            'Получен входящий POST callback',
            ApiConstants::LOG_LEVEL_INFO,
            $this->maskArray($this->postData)
        );

        // 1. Проверка HTTP-метода.
        $request_method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper(wp_unslash($_SERVER['REQUEST_METHOD'])) : 'GET';
        if ('POST' !== $request_method) {
            throw new CallbackException('Недопустимый метод HTTP-запроса.', 405);
        }

        // 2. Проверка наличия обязательных ключей.
        foreach (ApiConstants::CALLBACK_REQUIRED_KEYS as $key) {
            if (empty($this->postData[$key])) {
                $errorMsg = sprintf('Ошибка: в callback отсутствуют обязательные параметры. Нет ключа "%s".', $key);
                throw new CallbackException($errorMsg, 400);
            }
        }

        // 3. Проверка подписи.
        if (!$this->isSignatureCorrect()) {
            throw new CallbackException('Ошибка: Неверная подпись callback.', 403);
        }

        // 4. Извлечение и валидация ID заказа.
        $rawOrderId = $this->postData[ApiConstants::CB_PARAM_ORDERID];
        // Формат orderid: "ID_ЗАКАЗА|TIMESTAMP"
        $parts = explode('|', $rawOrderId);
        $orderId = isset($parts[0]) ? (int)$parts[0] : 0;

        if ($orderId <= 0) {
            $errorMsg = sprintf('Ошибка: Неверный формат orderid в callback: %s', $rawOrderId);
            throw new CallbackException($errorMsg, 400);
        }

        return [
            'order_id'       => $orderId,
            'transaction_id' => sanitize_text_field($this->postData[ApiConstants::CB_PARAM_ID]),
            'amount'         => (float)$this->postData[ApiConstants::CB_PARAM_SUM],
        ];
    }

    /**
     * Проверяет подпись запроса.
     *
     * @return bool True, если подпись верна, иначе false.
     */
    private function isSignatureCorrect(): bool
    {
        $post = $this->postData;

        $stringToHash = $post[ApiConstants::CB_PARAM_ID] .
            $post[ApiConstants::CB_PARAM_SUM] .
            $post[ApiConstants::CB_PARAM_CLIENTID] .
            $post[ApiConstants::CB_PARAM_ORDERID] .
            $post[ApiConstants::CB_PARAM_LOGIN] .
            $this->settings->getSecretKey();

        // Используем hash(), передавая константу.
        $expectedKey = hash(ApiConstants::CALLBACK_SIGNATURE_ALGO, $stringToHash);

        $this->logger->log(
            'Строка для проверки подписи: ' . $this->getMaskedStringToHash(),
            ApiConstants::LOG_LEVEL_DEBUG
        );

        return hash_equals($expectedKey, $post[ApiConstants::CB_PARAM_KEY]);
    }

    /**
     * Генерирует ответ для отправки банку после успешной обработки callback.
     *
     * @return string
     */
    public function getSuccessResponse(): string
    {
        $transactionId = $this->postData[ApiConstants::CB_PARAM_ID] ?? '';
        // Формируем OK-ответ с хешем транзакции и секретного ключа
        return 'OK ' . hash(ApiConstants::CALLBACK_SIGNATURE_ALGO, $transactionId . $this->settings->getSecretKey());
    }

    /**
     * Собирает строку для хеширования с замаскированными данными специально для логов.
     *
     * @return string
     */
    private function getMaskedStringToHash(): string
    {
        $post = $this->postData;

        return ($post[ApiConstants::CB_PARAM_ID] ?? '') .
            ($post[ApiConstants::CB_PARAM_SUM] ?? '') .
            ($post[ApiConstants::CB_PARAM_CLIENTID] ?? '') .
            ($post[ApiConstants::CB_PARAM_ORDERID] ?? '') .
            '****' . // Login замаскирован
            '****';  // Secret Key замаскирован
    }

    /**
     * Маскирует чувствительные данные в массиве для безопасного логирования.
     *
     * @param array<string, mixed> $array Исходный массив.
     * @return array<string, mixed> Массив с маскированными значениями.
     */
    private function maskArray(array $array): array
    {
        $keysToMask = [ApiConstants::CB_PARAM_LOGIN, ApiConstants::CB_PARAM_KEY, 'secret_key'];
        foreach ($keysToMask as $key) {
            if (isset($array[$key])) {
                $array[$key] = '****';
            }
        }
        return $array;
    }
}