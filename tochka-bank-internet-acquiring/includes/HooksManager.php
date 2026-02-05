<?php
// Файл: includes/HooksManager.php

declare(strict_types=1);

namespace Tochka\Woocommerce;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Utilities\FeaturesUtil;
use JetBrains\PhpStorm\NoReturn;
use Tochka\Woocommerce\Service\ApiConstants;

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
        // Добавляем самый ранний хук для перехвата URL
        $this->fix_malformed_failed_url_redirect();

        // Хуки, связанные с WooCommerce
        add_action('before_woocommerce_init', [$this, 'declare_wc_compatibility']);
        add_filter('woocommerce_payment_gateways', [$this, 'add_gateway_class']);
        add_action('woocommerce_blocks_loaded', [$this, 'init_blocks_support']);

        // Хуки, связанные с WordPress Core
        add_action('init', [$this, 'add_rewrite_endpoint']);
        add_action('template_redirect', [$this, 'handle_endpoint_request']);

        // Хуки для админ-панели
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'add_admin_styles'], 20);
            $plugin_basename = plugin_basename(TOCHKA_PLUGIN_PATH . 'tochka-bank-internet-acquiring.php');
            add_filter('plugin_action_links_' . $plugin_basename, [$this, 'add_settings_link']);
        }

        // Хуки для фронтенда
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_styles']);
    }

    /**
     * Перехватывает и исправляет некорректный URL возврата, который генерирует банк при отмене платежа.
     */
    private function fix_malformed_failed_url_redirect(): void
    {
        // Получаем запрошенный URI
        $request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';

        // Собираем строку, которую мы ищем (/wc-api/tochka_payments/&failed)
        $malformed_pattern = '/wc-api/' . Bootstrap::GATEWAY_ID . '/&failed';

        // Проверяем, содержит ли URI наш "сломанный" паттерн
        if (str_contains($request_uri, $malformed_pattern)) {
            // Заменяем его на корректный
            $correct_uri = str_replace($malformed_pattern, '/wc-api/' . Bootstrap::GATEWAY_ID . '/?status=failed', $request_uri);

            // Выполняем редирект на исправленный URL и прерываем выполнение
            if (!headers_sent()) {
                wp_safe_redirect($correct_uri);
                exit;
            }
        }
    }

    /**
     * [Hook] Объявляет совместимость с новыми функциями WooCommerce.
     */
    public function declare_wc_compatibility(): void
    {
        if (class_exists(FeaturesUtil::class)) {
            FeaturesUtil::declare_compatibility('custom_order_tables', TOCHKA_PLUGIN_PATH . 'tochka-bank-internet-acquiring.php');
            FeaturesUtil::declare_compatibility('cart_checkout_blocks', TOCHKA_PLUGIN_PATH . 'tochka-bank-internet-acquiring.php');
        }
    }

    /**
     * [Hook] Добавляет класс шлюза в список платежных шлюзов WooCommerce.
     *
     * @param array $gateways Массив зарегистрированных шлюзов.
     * @return array Обновленный массив шлюзов.
     */
    public function add_gateway_class(array $gateways): array
    {
        $gateways[] = Gateway::class;
        return $gateways;
    }

    /**
     * [Hook] Инициализирует поддержку WooCommerce Blocks.
     */
    public function init_blocks_support(): void
    {
        if (!class_exists(AbstractPaymentMethodType::class)) {
            return;
        }

        add_action(
                'woocommerce_blocks_payment_method_type_registration',
                function (PaymentMethodRegistry $payment_method_registry) {
                    $payment_method_registry->register(new BlocksSupport());
                }
        );
    }

    /**
     * [Hook] Регистрирует кастомный эндпоинт для обработки платежей.
     */
    public function add_rewrite_endpoint(): void
    {
        add_rewrite_endpoint(ApiConstants::ENDPOINT_AJAX, EP_ROOT);
    }

    /**
     * [Hook] Перехватывает запрос к эндпоинту и делегирует обработку классу шлюза.
     */
    #[NoReturn]
    public function handle_endpoint_request(): void
    {
        global $wp_query;

        if (!isset($wp_query->query_vars[ApiConstants::ENDPOINT_AJAX])) {
            return;
        }

        if (!isset($_GET['order_id']) || !isset($_GET['order_key'])) {
            wp_die('Invalid request', 'Error', 400);
        }

        $gateways = WC()->payment_gateways()->payment_gateways();

        if (isset($gateways[Bootstrap::GATEWAY_ID])) {
            /** @var Gateway $gateway */
            $gateway = $gateways[Bootstrap::GATEWAY_ID];
            $gateway->handleAjaxFormGeneration();
        } else {
            wp_die('Payment gateway not found.', 'Error', 500);
        }
    }

    /**
     * [Hook] Добавляет CSS-стили для страницы настроек шлюза в админ-панели.
     */
    public function add_admin_styles(): void
    {
        // Проверяем, что мы на нужной странице настроек
        if (
                !is_admin() ||
                !isset($_GET['page']) || 'wc-settings' !== $_GET['page'] ||
                !isset($_GET['tab']) || 'checkout' !== $_GET['tab'] ||
                !isset($_GET['section']) || Bootstrap::GATEWAY_ID !== $_GET['section']
        ) {
            return;
        }

        $gateway_id = Bootstrap::GATEWAY_ID;

        $css = '
            /* 
             * Стиль для заголовка-разделителя (h3).
             */
            body.woocommerce_page_wc-settings h3.tochka-settings-section-title {
                background: #A57BEC; /* Main Purple 5 */
                color: #FFFFFF;
                padding: 15px 25px;
                margin-top: 25px;
                margin-bottom: 0;
                border: 1px solid #915de6;
                border-bottom: none;
                border-radius: 8px 8px 0 0; /* Скругляем верхние углы */
                font-size: 1.2em;
                font-weight: 500;
            }

            /* 
             * Стиль для таблицы настроек, которая идет СРАЗУ ПОСЛЕ нашего заголовка.
             */
            body.woocommerce_page_wc-settings h3.tochka-settings-section-title + table.form-table {
                border-collapse: separate;
                border-spacing: 0;
                overflow: hidden;
                
                background: #FFFFFF;
                margin-top: 0;
                border: 1px solid #dce1e6;
                border-top: none;
                border-radius: 0 0 8px 8px;
                padding-top: 20px;
                padding-bottom: 20px;
            }

            /* 
             * Применяем боковые отступы ко всем ячейкам наших таблиц
             */
            body.woocommerce_page_wc-settings h3.tochka-settings-section-title + table.form-table th,
            body.woocommerce_page_wc-settings h3.tochka-settings-section-title + table.form-table td {
                padding-left: 25px;
                padding-right: 25px;
            }

            /* Убираем верхний отступ у самого первого блока настроек */
            body.woocommerce_page_wc-settings h3.tochka-settings-section-title:first-of-type {
                margin-top: 5px;
            }

            /* Прячем стандартное описание под главным заголовком */
            .wc-settings-content > p:first-of-type {
                display: none;
            }
            
            /* --- Стили для иконки "глаза" --- */

            /* 1. Контейнер-обертка для поля и иконки.
             */
            .tochka-password-wrapper {
                position: relative;
                display: inline-block;
            }

            /* 2. Добавляем отступ справа ВНУТРИ поля ввода, чтобы текст не заезжал под иконку. */
            .tochka-password-wrapper input[type="password"],
            .tochka-password-wrapper input[type="text"] {
                padding-right: 40px !important;
            }

            /* 3. Стили самой иконки.
             */
            .tochka-password-toggle {
                position: absolute;
                top: 50%;
                right: 10px;
                transform: translateY(-50%);
                cursor: pointer;
                color: #787c82;
            }
            .tochka-password-toggle:hover {
                color: #2271b1;
            }
        ';
        wp_add_inline_style('woocommerce_admin_styles', $css);

        // --- Блок JavaScript ---
        $toggle_title = esc_js(__('Показать/скрыть ключ', 'tochka-bank-internet-acquiring'));

        $js_code = "
        document.addEventListener('DOMContentLoaded', function() {
            const secretField = document.getElementById('woocommerce_" . esc_js($gateway_id) . "_secret_key');
            if (!secretField) {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'tochka-password-wrapper';
            secretField.parentNode.insertBefore(wrapper, secretField);
            wrapper.appendChild(secretField);

            const toggleIcon = document.createElement('span');
            toggleIcon.className = 'dashicons dashicons-visibility tochka-password-toggle';
            
            // Результат PHP-функций вставляется в JS-строку.
            toggleIcon.setAttribute('title', '" . $toggle_title . "'); 
            
            wrapper.appendChild(toggleIcon);

            toggleIcon.addEventListener('click', function() {
                const isPassword = secretField.type === 'password';
                secretField.type = isPassword ? 'text' : 'password';
                this.className = isPassword
                    ? 'dashicons dashicons-hidden tochka-password-toggle'
                    : 'dashicons dashicons-visibility tochka-password-toggle';
            });
        });
    ";
        wp_add_inline_script('wc-settings', $js_code);
    }

    /**
     * [Hook] Подключает CSS-стили для фронтенда.
     */
    public function enqueue_frontend_styles(): void
    {
        if (!is_checkout()) {
            return;
        }

        $css = "
            .payment_method_tochka_payments label img {
                max-height: 2.5em;
                width: auto;
                margin-left: 10px;
                display: inline-block;
                vertical-align: middle;
            }
        ";

        wp_add_inline_style('woocommerce-general', $css);
    }

    /**
     * [Hook] Добавляет ссылку "Настройки" на странице плагинов.
     *
     * @param array $links Массив существующих ссылок.
     * @return array Обновленный массив ссылок.
     */
    public function add_settings_link(array $links): array
    {
        $settings_link = '<a href="'
                . admin_url('admin.php?page=wc-settings&tab=checkout&section=' . Bootstrap::GATEWAY_ID)
                . '">'
                . __('Settings', 'tochka-bank-internet-acquiring')
                . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}