<?php
// Файл: includes/Service/Logger.php

declare(strict_types=1);

namespace Tochka\Woocommerce\Service;

use WC_Log_Levels;
use WC_Logger;

/**
 * Класс для логирования операций платежного модуля.
 */
final class Logger
{
    /**
     * @var WC_Logger|null Экземпляр логгера WooCommerce.
     */
    private static ?WC_Logger $wcLogger = null;

    /**
     * @var string Уровень логирования, установленный в настройках.
     */
    private string $loggingLevel;

    /**
     * @var string ID платежного шлюза для контекста логов.
     */
    private string $gatewayId;

    /**
     * Конструктор.
     *
     * @param string $gatewayId    ID шлюза.
     * @param string $loggingLevel Уровень логирования из настроек ('NONE', 'ERROR', 'INFO', 'DEBUG').
     */
    public function __construct(string $gatewayId, string $loggingLevel)
    {
        $this->gatewayId    = $gatewayId;
        $this->loggingLevel = $loggingLevel;
    }

    /**
     * Записывает сообщение в лог, если уровень сообщения соответствует настройкам.
     *
     * @param string              $message Сообщение для записи.
     * @param string              $level   Уровень сообщения ('ERROR', 'INFO', 'DEBUG').
     * @param array<string,mixed> $context Дополнительный контекст для записи.
     *
     */
    public function log(string $message, string $level = ApiConstants::LOG_LEVEL_INFO, array $context = []): void
    {
        $currentLevelValue = ApiConstants::LOG_LEVELS_MAP[$this->loggingLevel] ?? 2;
        $messageLevelValue = ApiConstants::LOG_LEVELS_MAP[$level] ?? 2;

        if ($currentLevelValue < $messageLevelValue) {
            return;
        }

        if (self::$wcLogger === null) {
            self::$wcLogger = wc_get_logger();
        }

        $context['source'] = $this->gatewayId;

        $wcLogLevel = strtolower($level);
        if ($wcLogLevel === 'error') {
            $wcLogLevel = WC_Log_Levels::ERROR;
        } elseif ($wcLogLevel === 'debug') {
            $wcLogLevel = WC_Log_Levels::DEBUG;
        } else {
            $wcLogLevel = WC_Log_Levels::INFO;
        }

        self::$wcLogger->log($wcLogLevel, $message, $context);
    }
}