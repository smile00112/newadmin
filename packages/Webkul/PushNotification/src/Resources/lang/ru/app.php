<?php

return [
    'settings' => [
        'title' => 'Push-уведомления',
        'info'  => 'Настройки push-уведомлений через Firebase Cloud Messaging',

        'general' => [
            'title' => 'Общие',
            'info'  => 'Общие настройки push-уведомлений',

            'settings' => [
                'title' => 'Настройки Firebase',
                'info'  => 'Параметры подключения к Firebase',
            ],

            'messages' => [
                'title' => 'Тексты уведомлений',
                'info'  => 'Настройки текста push-уведомлений для каждого статуса заказа',
            ],
        ],

        'fields' => [
            'enabled'                        => 'Включить push-уведомления',
            'enabled-info'                   => 'Включить отправку push-уведомлений через Firebase',
            'firebase-credentials-json'      => 'Firebase учётные данные (JSON)',
            'firebase-credentials-json-info' => 'Скопируйте содержимое файла Service Account JSON из Firebase Console',
            'firebase-project-id'            => 'Firebase Project ID',
            'firebase-project-id-info'       => 'ID проекта Firebase (находится в Service Account JSON)',
            'statuses'                       => 'Статусы для отправки push',
            'statuses-info'                  => 'Выберите статусы заказов, при изменении на которые будут отправляться push-уведомления',
            'placeholder-info'               => 'Поддерживает плейсхолдеры: {order_id}, {status_label}',

            'title-pending'         => 'Заголовок — «Новый»',
            'body-pending'          => 'Текст — «Новый»',
            'title-pending-payment' => 'Заголовок — «Ожидание оплаты»',
            'body-pending-payment'  => 'Текст — «Ожидание оплаты»',
            'title-processing'      => 'Заголовок — «Обработка»',
            'body-processing'       => 'Текст — «Обработка»',
            'title-preparing'       => 'Заголовок — «Готовим»',
            'body-preparing'        => 'Текст — «Готовим»',
            'title-ready'           => 'Заголовок — «Готов»',
            'body-ready'            => 'Текст — «Готов»',
            'title-completed'       => 'Заголовок — «Выполнен»',
            'body-completed'        => 'Текст — «Выполнен»',
            'title-canceled'        => 'Заголовок — «Отмена»',
            'body-canceled'         => 'Текст — «Отмена»',
            'title-closed'          => 'Заголовок — «Закрыт»',
            'body-closed'           => 'Текст — «Закрыт»',
        ],

        'order-status' => [
            'pending'         => 'Новый',
            'pending-payment' => 'Ожидание оплаты',
            'processing'      => 'Обработка',
            'preparing'       => 'Готовим',
            'ready'           => 'Готов',
            'completed'       => 'Выполнен',
            'canceled'        => 'Отмена',
            'closed'          => 'Закрыт',
        ],
    ],
];
