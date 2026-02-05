<?php
// Файл: includes/Service/ApiConstants.php

declare(strict_types=1);

namespace RemoteOnlinePayments\Service;

/**
 * Константы для работы с API.
 */
final class ApiConstants
{
    /**
     * Уровни логирования
     */
    public const LOG_LEVEL_NONE = 'NONE';
    public const LOG_LEVEL_ERROR = 'ERROR';
    public const LOG_LEVEL_INFO = 'INFO';
    public const LOG_LEVEL_DEBUG = 'DEBUG';

    /**
     * Мета-поле для хранения ссылки на оплату
     */
    public const META_PAYMENT_URL = '_remote_payment_url';

    /**
     * Мета-поле для хранения ID транзакции
     */
    public const META_TRANSACTION_ID = '_remote_transaction_id';

    /**
     * Поля для callback-запроса
     */
    public const CB_PARAM_ORDER_ID = 'order_id';
    public const CB_PARAM_STATUS = 'status';
    public const CB_PARAM_TRANSACTION_ID = 'transaction_id';
    public const CB_PARAM_AMOUNT = 'amount';
    public const CB_PARAM_MESSAGE = 'message';

    /**
     * Статусы оплаты
     */
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PENDING = 'pending';
}
