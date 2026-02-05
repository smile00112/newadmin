<?php
// Файл: includes/Exception/CallbackException.php

declare(strict_types=1);

namespace RemoteOnlinePayments\Exception;

use Exception;

/**
 * Исключение для обработки ошибок callback-запросов.
 */
final class CallbackException extends Exception
{
    /**
     * HTTP статус код для ответа
     */
    private int $httpStatusCode;

    /**
     * Конструктор.
     *
     * @param string $message Сообщение об ошибке
     * @param int $httpStatusCode HTTP статус код (по умолчанию 400)
     * @param int $code Код исключения
     * @param Exception|null $previous Предыдущее исключение
     */
    public function __construct(
        string $message = '',
        int $httpStatusCode = 400,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * Возвращает HTTP статус код.
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}
