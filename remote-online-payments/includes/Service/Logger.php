<?php
// Файл: includes/Service/Logger.php

declare(strict_types=1);

namespace RemoteOnlinePayments\Service;

use WC_Logger;

/**
 * Класс для логирования операций плагина.
 */
final class Logger
{
    /**
     * @var WC_Logger Экземпляр логгера WooCommerce
     */
    private WC_Logger $logger;

    /**
     * @var string ID платежного шлюза
     */
    private string $gatewayId;

    /**
     * @var string Уровень логирования
     */
    private string $loggingLevel;

    /**
     * Конструктор.
     *
     * @param string $gatewayId ID платежного шлюза
     * @param string $loggingLevel Уровень логирования
     */
    public function __construct(string $gatewayId, string $loggingLevel = ApiConstants::LOG_LEVEL_INFO)
    {
        $this->gatewayId = $gatewayId;
        $this->loggingLevel = $loggingLevel;
        $this->logger = wc_get_logger();
    }

    /**
     * Записывает сообщение в лог.
     *
     * @param string $message Сообщение для логирования
     * @param string $level Уровень логирования
     * @param array $context Дополнительный контекст
     */
    public function log(string $message, string $level = ApiConstants::LOG_LEVEL_INFO, array $context = []): void
    {
        // Проверяем, нужно ли логировать на данном уровне
        if (!$this->shouldLog($level)) {
            return;
        }

        $logMessage = $message;
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . wp_json_encode($this->maskSensitiveData($context));
        }

        $this->logger->log(
            $this->convertLevel($level),
            $logMessage,
            ['source' => $this->gatewayId]
        );
    }

    /**
     * Проверяет, нужно ли логировать на данном уровне.
     *
     * @param string $level Уровень логирования
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        if ($this->loggingLevel === ApiConstants::LOG_LEVEL_NONE) {
            return false;
        }

        $levels = [
            ApiConstants::LOG_LEVEL_ERROR => 1,
            ApiConstants::LOG_LEVEL_INFO => 2,
            ApiConstants::LOG_LEVEL_DEBUG => 3,
        ];

        $currentLevel = $levels[$this->loggingLevel] ?? 0;
        $messageLevel = $levels[$level] ?? 0;

        return $messageLevel <= $currentLevel;
    }

    /**
     * Преобразует уровень логирования плагина в уровень WooCommerce.
     *
     * @param string $level Уровень логирования плагина
     * @return string Уровень логирования WooCommerce
     */
    private function convertLevel(string $level): string
    {
        $map = [
            ApiConstants::LOG_LEVEL_ERROR => 'error',
            ApiConstants::LOG_LEVEL_INFO => 'info',
            ApiConstants::LOG_LEVEL_DEBUG => 'debug',
        ];

        return $map[$level] ?? 'info';
    }

    /**
     * Маскирует чувствительные данные в массиве.
     *
     * @param array $data Данные для маскировки
     * @return array Данные с замаскированными значениями
     */
    private function maskSensitiveData(array $data): array
    {
        $sensitiveKeys = ['api_key', 'secret_key', 'password', 'token', 'authorization'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '****';
            }
        }

        return $data;
    }
}
