<?php
// Файл: includes/Service/ApiConstants.php

declare(strict_types=1);

namespace Tochka\Woocommerce\Service;

/**
 * Класс-справочник для констант и значений, специфичных для API банка.
 */
final class ApiConstants
{
    /**
     * @var string Идентификатор платежной системы, отправляемый в банк.
     */
    public const SERVICE_NAME = 'tochka_woocommerce_payments';

    /**
     * @var string Имя эндпоинта для обработки AJAX-платежей.
     */
    public const ENDPOINT_AJAX = 'tochka-payment-handler';

    /**
     * @var float Минимально допустимая сумма для создания платежа.
     */
    public const MIN_PAYMENT_AMOUNT = 1.00;

    /**
     * @var string Алгоритм хеширования для подписи исходящего запроса (форма оплаты).
     */
    public const REQUEST_SIGNATURE_ALGO = 'sha256';

    /**
     * @var string Алгоритм хеширования для проверки подписи входящего callback.
     */
    public const CALLBACK_SIGNATURE_ALGO = 'md5';

    // --- Константы параметров Callback ---
    public const CB_PARAM_ID       = 'id';
    public const CB_PARAM_SUM      = 'sum';
    public const CB_PARAM_CLIENTID = 'clientid';
    public const CB_PARAM_ORDERID  = 'orderid';
    public const CB_PARAM_KEY      = 'key';
    public const CB_PARAM_LOGIN    = 'login';

    /**
     * @var array<string> Список обязательных полей в callback-запросе.
     */
    public const CALLBACK_REQUIRED_KEYS = [
        self::CB_PARAM_ID,
        self::CB_PARAM_SUM,
        self::CB_PARAM_CLIENTID,
        self::CB_PARAM_ORDERID,
        self::CB_PARAM_KEY,
        self::CB_PARAM_LOGIN,
    ];

    // --- Уровни логирования ---
    public const LOG_LEVEL_NONE  = 'NONE';
    public const LOG_LEVEL_ERROR = 'ERROR';
    public const LOG_LEVEL_INFO  = 'INFO';
    public const LOG_LEVEL_DEBUG = 'DEBUG';

    /**
     * @var array<string, int> Карта уровней логирования для сравнения.
     */
    public const LOG_LEVELS_MAP = [
        self::LOG_LEVEL_NONE  => 0,
        self::LOG_LEVEL_ERROR => 1,
        self::LOG_LEVEL_INFO  => 2,
        self::LOG_LEVEL_DEBUG => 3,
    ];
}