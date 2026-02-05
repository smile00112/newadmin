<?php
// Файл: includes/Admin/SettingsForm.php

declare(strict_types=1);

namespace RemoteOnlinePayments\Admin;

use RemoteOnlinePayments\Service\ApiConstants;

/**
 * Класс для формирования структуры полей настроек платежного шлюза.
 * Инкапсулирует всю логику, связанную с UI админ-панели.
 */
final class SettingsForm
{
    /**
     * Возвращает массив с описанием полей для формы настроек WooCommerce.
     *
     * @return array
     */
    public static function get_fields(): array
    {
        return [
            'main_settings_title' => [
                'title' => __('Основные настройки', 'remote-online-payments'),
                'type' => 'title',
            ],
            'enabled' => [
                'title' => __('Включить/Выключить', 'remote-online-payments'),
                'type' => 'checkbox',
                'label' => __('Включить платежный шлюз', 'remote-online-payments'),
                'default' => 'no',
            ],
            'title' => [
                'title' => __('Заголовок', 'remote-online-payments'),
                'type' => 'text',
                'description' => __('Название способа оплаты, которое увидит покупатель при оформлении заказа.', 'remote-online-payments'),
                'default' => __('Онлайн оплата', 'remote-online-payments'),
                'desc_tip' => true,
            ],
            'description' => [
                'title' => __('Описание', 'remote-online-payments'),
                'type' => 'textarea',
                'description' => __('Описание способа оплаты, которое покупатель увидит под заголовком.', 'remote-online-payments'),
                'default' => __('Оплата через сторонний сервер', 'remote-online-payments'),
                'desc_tip' => true,
            ],
            'api_settings_title' => [
                'title' => __('Настройки API', 'remote-online-payments'),
                'type' => 'title',
            ],
            'api_url' => [
                'title' => __('URL API сервера', 'remote-online-payments'),
                'type' => 'text',
                'description' => __('URL стороннего сервера для отправки данных заказа.', 'remote-online-payments'),
                'default' => '',
                'desc_tip' => true,
                'required' => true,
            ],
            'api_key' => [
                'title' => __('API ключ', 'remote-online-payments'),
                'type' => 'text',
                'description' => __('API ключ для аутентификации на стороннем сервере (опционально).', 'remote-online-payments'),
                'default' => '',
                'desc_tip' => true,
            ],
            'secret_key' => [
                'title' => __('Секретный ключ', 'remote-online-payments'),
                'type' => 'password',
                'description' => __('Секретный ключ для подписи запросов (опционально).', 'remote-online-payments'),
                'default' => '',
                'desc_tip' => true,
            ],
            'order_settings_title' => [
                'title' => __('Настройки заказа', 'remote-online-payments'),
                'type' => 'title',
            ],
            'completed_order_status' => [
                'title' => __('Статус заказа после оплаты', 'remote-online-payments'),
                'type' => 'select',
                'options' => wc_get_order_statuses(),
                'default' => 'wc-processing',
                'description' => __('Выберите статус, в который будет переведен заказ после получения успешного уведомления об оплате.', 'remote-online-payments'),
                'desc_tip' => true,
            ],
            'logging_title' => [
                'title' => __('Настройки логирования', 'remote-online-payments'),
                'type' => 'title',
            ],
            'logging_level' => [
                'title' => __('Уровень логирования', 'remote-online-payments'),
                'type' => 'select',
                'options' => [
                    ApiConstants::LOG_LEVEL_NONE => __('Выключено', 'remote-online-payments'),
                    ApiConstants::LOG_LEVEL_ERROR => __('Только ошибки', 'remote-online-payments'),
                    ApiConstants::LOG_LEVEL_INFO => __('Информационный (Рекомендуется)', 'remote-online-payments'),
                    ApiConstants::LOG_LEVEL_DEBUG => __('Отладка (Полный лог)', 'remote-online-payments'),
                ],
                'default' => ApiConstants::LOG_LEVEL_INFO,
                'description' => __('Выберите, насколько подробную информацию записывать в лог-файл (в wp-content/uploads/wc-logs/).', 'remote-online-payments'),
                'desc_tip' => true,
            ],
        ];
    }
}
