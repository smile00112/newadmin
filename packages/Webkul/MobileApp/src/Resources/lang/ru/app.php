<?php

return [
    'settings' => [
        'title'        => 'Мобильное приложение',
        'info'         => 'Настройки для мобильного приложения',
        'save-success' => 'Настройки успешно сохранены',

        'general' => [
            'title' => 'Общие',
            'info'  => 'Общие настройки мобильного приложения',

            'app-info' => [
                'title' => 'Информация о приложении',
                'info'  => 'Основная информация о приложении',
            ],

            'custom' => [
                'title' => 'Пользовательские настройки',
                'info'  => 'Пользовательские данные конфигурации',
            ],

            'contact-us' => [
                'title' => 'Напишите нам',
                'info'  => 'Контактная информация для мобильного приложения',
            ],

            'documents' => [
                'title' => 'Ссылки на документы',
                'info'  => 'Ссылки на документы для мобильного приложения',
            ],
        ],

        'fields' => [
            'app-name'              => 'Название приложения',
            'app-name-info'         => 'Название мобильного приложения',
            'app-version'           => 'Версия приложения',
            'app-version-info'      => 'Текущая версия приложения',
            'min-app-version'       => 'Минимальная версия',
            'min-app-version-info'  => 'Минимальная требуемая версия',
            'force-update'          => 'Принудительное обновление',
            'force-update-info'     => 'Принудительно обновлять приложение',
            'maintenance-mode'      => 'Режим обслуживания',
            'maintenance-mode-info' => 'Включить режим обслуживания для мобильного приложения',
            'custom-data'           => 'Пользовательские данные (JSON)',
            'custom-data-info'      => 'Пользовательские JSON данные для мобильного приложения',

            'featured-categories'      => 'Избранные категории',
            'featured-categories-info' => 'Выберите категории для отображения в мобильном приложении',
            'featured-products'        => 'Избранные товары',
            'featured-products-info'   => 'Выберите товары для отображения в мобильном приложении',
            'default-channel'          => 'Канал по умолчанию',
            'default-channel-info'     => 'Канал продаж по умолчанию для мобильного приложения',
            'shipping-methods'         => 'Методы доставки',
            'shipping-methods-info'    => 'Доступные методы доставки в мобильном приложении',
            'home-filters'             => 'Фильтры главного экрана',
            'home-filters-info'        => 'Выберите атрибуты для фильтрации на главном экране',
            'contact-telegram'         => 'Telegram контакт',
            'contact-telegram-info'    => 'Ссылка на Telegram контакт',
            'contact-whatsapp'         => 'WhatsApp контакт',
            'contact-whatsapp-info'    => 'Ссылка на WhatsApp контакт',
            'contact-email'            => 'Email',
            'contact-email-info'       => 'Адрес электронной почты',
            'contact-max'              => 'Max мессенджер контакт',
            'contact-max-info'         => 'Ссылка на Max мессенджер контакт',
            'user-agreement'            => 'Пользовательское соглашение',
            'user-agreement-info'       => 'Выберите CMS страницу для пользовательского соглашения',
            'privacy-policy'            => 'Условия конфиденциальности',
            'privacy-policy-info'       => 'Выберите CMS страницу для условий конфиденциальности',

            'push-enabled'                     => 'Включить push-уведомления',
            'push-enabled-info'                => 'Включить отправку push-уведомлений через Firebase',
            'firebase-credentials-json'        => 'Firebase учётные данные (JSON)',
            'firebase-credentials-json-info'   => 'Скопируйте содержимое файла Service Account JSON из Firebase Console',
            'firebase-project-id'              => 'Firebase Project ID',
            'firebase-project-id-info'         => 'ID проекта Firebase (находится в Service Account JSON)',
            'push-statuses'                    => 'Статусы для отправки push',
            'push-statuses-info'               => 'Выберите статусы заказов, при изменении на которые будут отправляться push-уведомления',
            'push-info'                        => 'Может содержать плейсхолдеры: {order_id}, {status_label}',
            'push-body-info'                   => 'Полный текст уведомления (может содержать плейсхолдеры: {order_id}, {status_label})',

            'push-title-pending'               => 'Заголовок для статуса "Новый"',
            'push-body-pending'                => 'Текст для статуса "Новый"',
            'push-title-pending-payment'       => 'Заголовок для статуса "Ожидание оплаты"',
            'push-body-pending-payment'        => 'Текст для статуса "Ожидание оплаты"',
            'push-title-processing'            => 'Заголовок для статуса "Обработка"',
            'push-body-processing'             => 'Текст для статуса "Обработка"',
            'push-title-preparing'             => 'Заголовок для статуса "Готовим"',
            'push-body-preparing'              => 'Текст для статуса "Готовим"',
            'push-title-ready'                 => 'Заголовок для статуса "Готов"',
            'push-body-ready'                  => 'Текст для статуса "Готов"',
            'push-title-completed'             => 'Заголовок для статуса "Выполнен"',
            'push-body-completed'              => 'Текст для статуса "Выполнен"',
            'push-title-canceled'              => 'Заголовок для статуса "Отмена"',
            'push-body-canceled'               => 'Текст для статуса "Отмена"',
            'push-title-closed'                => 'Заголовок для статуса "Закрыт"',
            'push-body-closed'                 => 'Текст для статуса "Закрыт"',
        ],

        'push-notifications' => [
            'title' => 'Push-уведомления',
            'info'  => 'Настройки отправки push-уведомлений через Firebase Cloud Messaging',

            'settings' => [
                'title' => 'Настройки Firebase',
                'info'  => 'Основные параметры для подключения Firebase',
            ],

            'messages' => [
                'title' => 'Текст уведомлений',
                'info'  => 'Настройки текста push-уведомлений для каждого статуса заказа',
            ],
        ],

        'order-status' => [
            'pending'           => 'Новый',
            'pending-payment'   => 'Ожидание оплаты',
            'processing'        => 'Обработка',
            'preparing'         => 'Готовим',
            'ready'             => 'Готов',
            'completed'         => 'Выполнен',
            'canceled'          => 'Отмена',
            'closed'            => 'Закрыт',
        ],
    ],

    'acl' => [
        'settings' => 'Настройки мобильного приложения',
        'edit'     => 'Редактировать',
    ],
];

