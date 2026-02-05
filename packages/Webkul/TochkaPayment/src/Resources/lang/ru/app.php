<?php

return [
    'admin' => [
        'menu' => [
            'tochka-payment' => 'Tochka Payment',
            'payment-history' => 'История платежей',
        ],
        'payment-history' => [
            'index' => [
                'title' => 'История платежей Tochka',
                'id' => 'ID',
                'order_id' => 'ID заказа',
                'amount' => 'Сумма',
                'client' => 'Клиент',
                'status' => 'Статус',
                'created_at' => 'Создан',
                'actions' => 'Действия',
                'view' => 'Просмотр',
                'empty' => 'Платежи не найдены',
            ],
            'show' => [
                'title' => 'Платеж #:id',
                'payment-info' => 'Информация о платеже',
                'client-info' => 'Информация о клиенте',
                'webhook-info' => 'Информация о webhook',
                'payment-id' => 'ID платежа',
                'order-id' => 'ID заказа',
                'external-order-id' => 'Внешний ID заказа',
                'transaction-id' => 'ID транзакции',
                'amount' => 'Сумма',
                'status' => 'Статус',
                'created-at' => 'Создан',
                'updated-at' => 'Обновлен',
                'client-name' => 'Имя клиента',
                'client-email' => 'Email клиента',
                'client-phone' => 'Телефон клиента',
                'payment-url' => 'Ссылка на оплату',
                'webhook-sent' => 'Webhook отправлен',
                'webhook-attempts' => 'Попыток отправки',
                'webhook-response' => 'Ответ сервера',
                'request-data' => 'Данные запроса',
                'callback-data' => 'Данные callback',
            ],
            'status' => [
                'pending' => 'Ожидает оплаты',
                'paid' => 'Оплачен',
                'failed' => 'Ошибка',
                'cancelled' => 'Отменен',
            ],
        ],
    ],
];
