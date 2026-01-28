<?php

use Webkul\Sales\Models\Order;

return [
    [
        'key'  => 'bonus_system',
        'name' => 'Бонусная система',
        'info' => 'Настройки бонусной системы и кешбека',
        'icon' => 'settings/bonus.svg',
        'sort' => 10,
    ], [
        'key'    => 'bonus_system.general',
        'name'   => 'Общие настройки',
        'info'   => 'Основные параметры бонусной системы',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'enabled',
                'title'   => 'Включить бонусную систему',
                'type'    => 'boolean',
                'default' => false,
            ], [
                'name'    => 'calculation_type',
                'title'   => 'Тип расчета уровня',
                'type'    => 'select',
                'default' => 'orders',
                'options' => [
                    [
                        'title' => 'От количества заказов',
                        'value' => 'orders',
                    ], [
                        'title' => 'От суммы потраченных средств',
                        'value' => 'amount',
                    ], [
                        'title' => 'От величины текущей корзины',
                        'value' => 'cart_value',
                    ],
                ],
            ], [
                'name'    => 'accrual_status',
                'title'   => 'Статусы заказа для начисления бонусов',
                'type'    => 'multiselect',
                'default' => [Order::STATUS_COMPLETED],
                'options' => [
                    [
                        'title' => Order::STATUS_PENDING,
                        'value' => Order::STATUS_PENDING,
                    ], [
                        'title' => Order::STATUS_PROCESSING,
                        'value' => Order::STATUS_PROCESSING,
                    ], [
                        'title' => Order::STATUS_COMPLETED,
                        'value' => Order::STATUS_COMPLETED,
                    ], [
                        'title' => Order::STATUS_PREPARING,
                        'value' => Order::STATUS_PREPARING,
                    ], [
                        'title' => Order::STATUS_READY,
                        'value' => Order::STATUS_READY,
                    ],
                ],
            ], [
                'name'    => 'deduction_status',
                'title'   => 'Статусы заказа для списания бонусов',
                'type'    => 'multiselect',
                'default' => [Order::STATUS_COMPLETED],
                'options' => [
                    [
                        'title' => Order::STATUS_PENDING,
                        'value' => Order::STATUS_PENDING,
                    ], [
                        'title' => Order::STATUS_PROCESSING,
                        'value' => Order::STATUS_PROCESSING,
                    ], [
                        'title' => Order::STATUS_COMPLETED,
                        'value' => Order::STATUS_COMPLETED,
                    ],
                ],
            ], [
                'name'    => 'refund_status',
                'title'   => 'Статусы заказа для возврата бонусов',
                'type'    => 'multiselect',
                'default' => [Order::STATUS_CANCELED],
                'options' => [
                    [
                        'title' => Order::STATUS_CANCELED,
                        'value' => Order::STATUS_CANCELED,
                    ], [
                        'title' => Order::STATUS_CLOSED,
                        'value' => Order::STATUS_CLOSED,
                    ],
                ],
            ], [
                'name'    => 'bonus_expiration_days',
                'title'   => 'Срок действия бонусов (дни)',
                'type'    => 'text',
                'default' => '365',
                'validation' => 'numeric|min:0',
            ], [
                'name'    => 'max_bonus_percent',
                'title'   => 'Максимальный процент оплаты бонусами',
                'type'    => 'text',
                'default' => '100',
                'validation' => 'numeric|min:0|max:100',
            ], [
                'name'    => 'included_products',
                'title'   => 'ID товаров, участвующих в бонусной системе (через запятую, если пусто - все)',
                'type'    => 'text',
                'default' => '',
            ], [
                'name'    => 'excluded_products',
                'title'   => 'ID товаров, исключенных из бонусной системы (через запятую)',
                'type'    => 'text',
                'default' => '',
            ],
        ],
    ],
];
