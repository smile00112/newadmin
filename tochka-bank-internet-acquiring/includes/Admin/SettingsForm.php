<?php
// Файл: includes/Admin/SettingsForm.php

declare(strict_types=1);

namespace Tochka\Woocommerce\Admin;

use Tochka\Woocommerce\Service\ApiConstants;

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
                'title' => __('Основные настройки', 'tochka-bank-internet-acquiring'),
                'type'  => 'title',
                'class' => 'tochka-settings-section-title',
            ],
            'enabled' => [
                'title'   => __('Включить/Выключить', 'tochka-bank-internet-acquiring'),
                'type'    => 'checkbox',
                'label'   => __('Включить платежный шлюз Точка Банк', 'tochka-bank-internet-acquiring'),
                'default' => 'no',
            ],
            'title' => [
                'title'       => __('Заголовок', 'tochka-bank-internet-acquiring'),
                'type'        => 'text',
                'description' => __('Название способа оплаты, которое увидит покупатель при оформлении заказа.', 'tochka-bank-internet-acquiring'),
                'default'     => __('Банковские карты и СБП', 'tochka-bank-internet-acquiring'),
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __('Описание', 'tochka-bank-internet-acquiring'),
                'type'        => 'textarea',
                'description' => __('Описание способа оплаты, которое покупатель увидит под заголовком.', 'tochka-bank-internet-acquiring'),
                'default'     => __('Приём платежей обеспечивает платёжная платформа Tochka', 'tochka-bank-internet-acquiring'),
            ],
            'credentials_title' => [
                'title' => __('Учетные данные', 'tochka-bank-internet-acquiring'),
                'type'  => 'title',
                'class' => 'tochka-settings-section-title',
            ],
            'tochka_server_url' => [
                'title'       => __('Адрес формы оплаты', 'tochka-bank-internet-acquiring'),
                'type'        => 'text',
                'description' => __('URL платёжной формы. По умолчанию: https://merch.tochka.com/woocommerce.', 'tochka-bank-internet-acquiring'),
                'default'     => 'https://merch.tochka.com/woocommerce',
                'desc_tip'    => true,
            ],
            'login' => [
                'title'       => __('Логин', 'tochka-bank-internet-acquiring'),
                'type'        => 'text',
                'description' => __('Ваш уникальный логин для интеграции из личного кабинета Точка Банка.', 'tochka-bank-internet-acquiring'),
                'desc_tip'    => true,
            ],
            'secret_key' => [
                'title'       => __('Секретное слово', 'tochka-bank-internet-acquiring'),
                'type'        => 'password',
                'description' => __('Секретный ключ для интеграции из личного кабинета Точка Банка.', 'tochka-bank-internet-acquiring'),
                'desc_tip'    => true,
            ],
            'fiscalization_title' => [
                'title' => __('Настройки фискализации (54-ФЗ)', 'tochka-bank-internet-acquiring'),
                'type'  => 'title',
                'class' => 'tochka-settings-section-title',
            ],
            'cart_enable' => [
                'title'   => __('Передавать данные для чека', 'tochka-bank-internet-acquiring'),
                'type'    => 'checkbox',
                'label'   => __('Включить передачу состава заказа (корзины) для фискализации.', 'tochka-bank-internet-acquiring'),
                'default' => 'yes',
            ],
            'default_vat' => [
                'title'       => __('Ставка НДС по умолчанию', 'tochka-bank-internet-acquiring'),
                'type'        => 'select',
                'options'     => [
                    'none'   => __('Без НДС', 'tochka-bank-internet-acquiring'),
                    'vat0'   => __('НДС 0%', 'tochka-bank-internet-acquiring'),
                    'vat5'   => __('НДС 5%', 'tochka-bank-internet-acquiring'),
                    'vat7'   => __('НДС 7%', 'tochka-bank-internet-acquiring'),
                    'vat10'  => __('НДС 10%', 'tochka-bank-internet-acquiring'),
                    'vat22'  => __('НДС 22%', 'tochka-bank-internet-acquiring'),
                    'vat105' => __('НДС 5/105', 'tochka-bank-internet-acquiring'),
                    'vat107' => __('НДС 7/107', 'tochka-bank-internet-acquiring'),
                    'vat110' => __('НДС 10/110', 'tochka-bank-internet-acquiring'),
                    'vat122' => __('НДС 22/122', 'tochka-bank-internet-acquiring'),
                ],
                'default'     => 'none',
                'description' => __('Используется, если ставка НДС не задана для товара или доставки.', 'tochka-bank-internet-acquiring'),
                'desc_tip'    => true,
            ],
            'default_fiscal_item_type' => [
                'title'       => __('Признак предмета расчета по умолчанию', 'tochka-bank-internet-acquiring'),
                'type'        => 'select',
                'options'     => [
                    'goods'   => __('Товар', 'tochka-bank-internet-acquiring'),
                    'service' => __('Услуга', 'tochka-bank-internet-acquiring'),
                ],
                'default'     => 'goods',
                'description' => __('Используется для позиций в чеке, если для товара не указан индивидуальный признак через атрибут `fiscal-item-type`.', 'tochka-bank-internet-acquiring'),
                'desc_tip'    => true,
            ],
            'ux_title' => [
                'title' => __('Пользовательский опыт', 'tochka-bank-internet-acquiring'),
                'type'  => 'title',
                'class' => 'tochka-settings-section-title',
            ],
            'auto_redirect' => [
                'title'   => __('Автоматический редирект', 'tochka-bank-internet-acquiring'),
                'type'    => 'checkbox',
                'label'   => __('Сразу перенаправлять покупателя на страницу оплаты без промежуточной страницы.', 'tochka-bank-internet-acquiring'),
                'default' => 'yes',
            ],
            'ajax_checkout_support' => [
                'title'       => __('Поддержка AJAX-чекаута', 'tochka-bank-internet-acquiring'),
                'type'        => 'checkbox',
                'label'       => __('Включить для тем, использующих AJAX при оформлении заказа (например, блочный чекаут).', 'tochka-bank-internet-acquiring'),
                'default'     => 'yes',
                'description' => __('Если после оформления заказа не происходит автоматический редирект на оплату, включите эту опцию.', 'tochka-bank-internet-acquiring'),
                'desc_tip'    => true,
            ],
            'completed_order_status' => [
                'title'       => __('Статус заказа после оплаты', 'tochka-bank-internet-acquiring'),
                'type'        => 'select',
                'options'     => wc_get_order_statuses(),
                'default'     => 'wc-processing',
                'description' => __('Выберите статус, в который будет переведен заказ после получения успешного уведомления об оплате.', 'tochka-bank-internet-acquiring'),
                'desc_tip'    => true,
            ],
            'logging_title' => [
                'title' => __('Настройки логирования', 'tochka-bank-internet-acquiring'),
                'type'  => 'title',
                'class' => 'tochka-settings-section-title',
            ],
            'logging_level' => [
                'title'       => __('Уровень логирования', 'tochka-bank-internet-acquiring'),
                'type'        => 'select',
                'options'     => [
                    ApiConstants::LOG_LEVEL_NONE  => __('Выключено', 'tochka-bank-internet-acquiring'),
                    ApiConstants::LOG_LEVEL_ERROR => __('Только ошибки', 'tochka-bank-internet-acquiring'),
                    ApiConstants::LOG_LEVEL_INFO  => __('Информационный (Рекомендуется)', 'tochka-bank-internet-acquiring'),
                    ApiConstants::LOG_LEVEL_DEBUG => __('Отладка (Полный лог)', 'tochka-bank-internet-acquiring'),
                ],
                'default'     => ApiConstants::LOG_LEVEL_INFO,
                'description' => __('Выберите, насколько подробную информацию записывать в лог-файл (в wp-content/uploads/wc-logs/).', 'tochka-bank-internet-acquiring'),
                'desc_tip'    => true,
            ],
        ];
    }
}