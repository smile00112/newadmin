<?php
// Файл: includes/HooksManager.php

declare(strict_types=1);

namespace RemoteOnlinePayments;

use Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * Класс для управления хуками WordPress и WooCommerce.
 * Инкапсулирует всю логику, связанную с регистрацией и обработкой событий.
 */
final class HooksManager
{
    /**
     * Регистрирует все необходимые хуки для работы плагина.
     */
    public function init_hooks(): void
    {
        // Хуки, связанные с WooCommerce
        add_action('before_woocommerce_init', [$this, 'declare_wc_compatibility']);
        add_filter('woocommerce_payment_gateways', [$this, 'add_gateway_class']);

        // Хуки для админ-панели
        if (is_admin()) {
            $plugin_basename = plugin_basename(REMOTE_PAYMENTS_PLUGIN_PATH . 'remote-online-payments.php');
            add_filter('plugin_action_links_' . $plugin_basename, [$this, 'add_settings_link']);
        }
    }

    /**
     * [Hook] Объявляет совместимость с новыми функциями WooCommerce.
     */
    public function declare_wc_compatibility(): void
    {
        if (class_exists(FeaturesUtil::class)) {
            FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                REMOTE_PAYMENTS_PLUGIN_PATH . 'remote-online-payments.php',
                true
            );
            FeaturesUtil::declare_compatibility(
                'cart_checkout_blocks',
                REMOTE_PAYMENTS_PLUGIN_PATH . 'remote-online-payments.php',
                true
            );
        }
    }

    /**
     * [Hook] Добавляет класс шлюза в список платежных шлюзов WooCommerce.
     *
     * @param array $gateways Массив зарегистрированных шлюзов
     * @return array Обновленный массив шлюзов
     */
    public function add_gateway_class(array $gateways): array
    {
        $gateways[] = Gateway::class;
        return $gateways;
    }

    /**
     * [Hook] Добавляет ссылку "Настройки" на странице плагинов.
     *
     * @param array $links Массив существующих ссылок
     * @return array Обновленный массив ссылок
     */
    public function add_settings_link(array $links): array
    {
        $settings_link = '<a href="'
            . admin_url('admin.php?page=wc-settings&tab=checkout&section=' . Bootstrap::GATEWAY_ID)
            . '">'
            . __('Настройки', 'remote-online-payments')
            . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
