<?php
// Файл: includes/Exception/CallbackException.php

declare(strict_types=1);

namespace Tochka\Woocommerce\Exception;

use Exception;
use Throwable;

/**
 * Кастомный класс исключений для ошибок, возникающих при обработке callback.
 * Позволяет точечно отлавливать именно эти ошибки.
 */
class CallbackException extends Exception
{
    /**
     * @var int HTTP-статус, который следует вернуть в случае этой ошибки.
     */
    private int $httpStatusCode;

    /**
     * Конструктор.
     *
     * @param string          $message        Сообщение об ошибке.
     * @param int             $httpStatusCode HTTP-статус (например, 400, 403, 500).
     * @param int             $code           Код ошибки.
     * @param Throwable|null $previous       Предыдущее исключение.
     */
    public function __construct(string $message = "", int $httpStatusCode = 400, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * Возвращает рекомендованный HTTP-статус для ответа.
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}