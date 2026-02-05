<?php
// Файл: includes/Gateway.php

declare(strict_types=1);

namespace Tochka\Woocommerce;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use WC_Order;
use WC_Payment_Gateway;
use WC_Admin_Settings;
use Tochka\Woocommerce\Admin\SettingsForm;
use Tochka\Woocommerce\Service\ApiConstants;
use Tochka\Woocommerce\Service\CallbackHandler;
use Tochka\Woocommerce\Service\Logger;
use Tochka\Woocommerce\Service\RequestBuilder;
use Tochka\Woocommerce\Service\Settings;
use Tochka\Woocommerce\Service\OrderProcessor;
use Tochka\Woocommerce\Exception\CallbackException;

/**
 * Основной класс платежного шлюза.
 */
final class Gateway extends WC_Payment_Gateway
{
    /**
     * @var Settings|null Кэшированный DTO с настройками.
     */
    private ?Settings $settingsDto = null;

    /**
     * @var Logger|null Кэшированный экземпляр логгера.
     */
    private ?Logger $logger = null;

    /**
     * Конструктор класса.
     * Инициализирует основные свойства шлюза и регистрирует необходимые хуки.
     */
    public function __construct()
    {
        $this->id = Bootstrap::GATEWAY_ID;
        $locale = get_locale();
        if ($locale === 'ru_RU') {
            $this->icon = TOCHKA_PLUGIN_URL . 'assets/images/tochka-ru.svg';
        } else {
            $this->icon = TOCHKA_PLUGIN_URL . 'assets/images/tochka-en.svg';
        }
        $this->method_title = __('Точка Банк: Интернет-эквайринг', 'tochka-bank-internet-acquiring');
        $this->method_description = __('Платёжный плагин для интеграции с интернет-эквайрингом Точка Банка.', 'tochka-bank-internet-acquiring');
        $this->has_fields = false;
        $this->supports = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title', __('Банковские карты и СБП', 'tochka-bank-internet-acquiring'));
        $this->description = $this->get_option('description', __('Приём платежей обеспечивает платёжная платформа Tochka', 'tochka-bank-internet-acquiring'));
        $this->enabled = $this->get_option('enabled');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_receipt_' . $this->id, [$this, 'generate_payment_form_on_receipt_page']);
        add_action('woocommerce_api_' . strtolower($this->id), [$this, 'handle_callback']);
    }

    /**
     * Инициализирует поля формы настроек для админ-панели WooCommerce.
     * Делегирует получение структуры полей классу SettingsForm.
     */
    public function init_form_fields(): void
    {
        $this->form_fields = SettingsForm::get_fields();
    }

    /**
     * Обрабатывает платеж и возвращает результат для WooCommerce.
     *
     * @param int $order_id ID заказа.
     * @return array{result: string, redirect: string}
     * @throws Exception В случае ошибки, которая будет показана пользователю.
     */
    public function process_payment($order_id): array
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception(__('Заказ не найден.', 'tochka-bank-internet-acquiring'));
        }

        $this->getLogger()->log(sprintf('Начало обработки платежа для заказа #%d.', $order_id));

        if ($order->get_total() < ApiConstants::MIN_PAYMENT_AMOUNT) {
            /* translators: %s: Minimum payment amount with currency symbol. */
            $errorMsg = sprintf(__('Сумма заказа слишком мала. Минимальная сумма — %s.', 'tochka-bank-internet-acquiring'), wc_price(ApiConstants::MIN_PAYMENT_AMOUNT));
            $this->getLogger()->log(sprintf('ОШИБКА: Попытка оплаты заказа #%d с суммой меньше минимальной.', $order_id), ApiConstants::LOG_LEVEL_ERROR);
            throw new Exception($errorMsg);
        }

        $redirectUrl = $this->getSettings()->isAjaxCheckoutSupport()
            ? $this->getAjaxRedirectUrl($order)
            : $order->get_checkout_payment_url(true);

        return ['result'   => 'success', 'redirect' => $redirectUrl];
    }

    /**
     * Генерирует HTML-форму на промежуточной странице `order-pay`.
     * Используется для тем без AJAX-редиректа.
     *
     * @param int $orderId ID заказа.
     */
    public function generate_payment_form_on_receipt_page(int $orderId): void
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            return;
        }

        $this->getLogger()->log(sprintf('Генерация HTML-формы на странице order-pay для заказа #%d.', $orderId));

        $requestBuilder = new RequestBuilder($order, $this->getSettings(), $this->getLogger());
        $formData = $requestBuilder->getParams();

        echo '<p>' . esc_html__('Вы будете перенаправлены на страницу оплаты...', 'tochka-bank-internet-acquiring') . '</p>';
        echo '<form action="' . esc_url($this->getSettings()->getServerUrl()) . '" method="POST" name="tochka_payment_form" accept-charset="utf-8">';

        foreach ($formData as $key => $value) {
            echo '<input type="hidden" name="' . esc_attr($key) . '" value=\'' . esc_attr((string)$value) . '\'>';
        }

        echo '<input type="submit" class="button" value="' . esc_attr__('Оплатить', 'tochka-bank-internet-acquiring') . '">';
        echo '</form>';

        if ($this->getSettings()->isAutoRedirect()) {
            wp_add_inline_script('jquery', 'document.addEventListener("DOMContentLoaded", function(){ if(document.tochka_payment_form) { document.tochka_payment_form.submit(); } });');
        }
    }

    /**
     * Обрабатывает входящие запросы от банка (callback/return).
     * Вызывается через хук `woocommerce_api_{$this->id}`.
     */
    #[NoReturn]
    public function handle_callback(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->handle_return_from_bank();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle_payment_notification();
            exit;
        }

        wp_die('Invalid request method', 'Error', ['response' => 405]);
    }

    /**
     * Генерирует и выводит HTML-страницу с авто-сабмитом формы для AJAX-чекаута.
     * Вызывается из HooksManager через кастомный эндпоинт.
     */
    #[NoReturn]
    public function handleAjaxFormGeneration(): void
    {
        // Если nonce неверен или устарел, wp_verify_nonce вернет false.
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, 'tochka_pay_nonce')) {
            $this->getLogger()->log('Ошибка безопасности: Неверный nonce при попытке генерации формы.', ApiConstants::LOG_LEVEL_ERROR);
            wp_die('Security check failed (Nonce validation).', 'Error', ['response' => 403]);
        }

        $orderId = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        $orderKey = isset($_GET['order_key']) ? wc_clean(wp_unslash($_GET['order_key'])) : '';
        $order = wc_get_order($orderId);

        if (!$order || !$order->key_is_valid($orderKey)) {
            $this->getLogger()->log(sprintf('ОШИБКА: Неверные данные для AJAX-генерации формы. Order ID: %d',
                $orderId), ApiConstants::LOG_LEVEL_ERROR);
            wp_redirect(wc_get_checkout_url());
            exit;
        }

        $this->getLogger()->log(sprintf('AJAX: Генерация HTML-формы для заказа #%d.', $orderId));

        $requestBuilder = new RequestBuilder($order, $this->getSettings(), $this->getLogger());
        $formData = $requestBuilder->getParams();

        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Redirecting...</title></head><body>';
        echo '<form action="' . esc_url($this->getSettings()->getServerUrl()) .
            '" method="POST" name="tochka_payment_form_ajax" accept-charset="utf-8">';

        foreach ($formData as $key => $value) {
            echo '<input type="hidden" name="' . esc_attr($key) . '" value=\'' . esc_attr((string)$value) . '\'>';
        }

        echo '</form>';

        if (!wp_script_is('tochka-redirect', 'registered')) {
            wp_register_script(
                'tochka-redirect',
                TOCHKA_PLUGIN_URL . 'assets/js/tochka-redirect.js',
                [],
                TOCHKA_VERSION
            );
        }

        wp_print_scripts('tochka-redirect');

        echo '</body></html>';
        exit;
    }

    /**
     * Обрабатывает POST-уведомление об оплате от банка.
     */
    private function handle_payment_notification(): void
    {
        $handler = new CallbackHandler($_POST, $this->getSettings(), $this->getLogger());

        try {
            // 1. Валидируем входящий запрос
            $paymentResult = $handler->process();

            // 2. Создаем обработчик заказа и передаем ему данные
            $orderProcessor = new OrderProcessor($this->getSettings(), $this->getLogger());
            $orderProcessor->processSuccessfulPayment($paymentResult);

        } catch (CallbackException $e) {
            // Ошибки валидации callback
            $this->getLogger()->log($e->getMessage(), ApiConstants::LOG_LEVEL_ERROR);
            wp_die($e->getMessage(), 'Callback Error', ['response' => $e->getHttpStatusCode()]);
        } catch (Exception $e) {
            // Ошибки обработки заказа
            // Лог уже был записан внутри OrderProcessor, здесь только отвечаем банку
            wp_die($e->getMessage(), 'Order Processing Error', ['response' => $e->getCode() ?: 400]);
        }

        // Если все прошло успешно, отправляем банку OK-ответ
        echo $handler->getSuccessResponse();
        exit;
    }



    /**
     * Обрабатывает возврат пользователя на сайт с платежной страницы.
     * Перенаправляет пользователя на страницу просмотра заказа, где он может
     * увидеть актуальный статус платежа.
     */
    #[NoReturn]
    private function handle_return_from_bank(): void
    {
        $this->getLogger()->log('Получен GET-запрос на URL возврата, начинаем обработку.');

        $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
        $orderKey = isset($_GET['order_key']) ? wc_clean(wp_unslash($_GET['order_key'])) : '';

        $this->getLogger()->log(sprintf('Данные из GET-запроса: Order ID = %d, Order Key = %s', $orderId, $orderKey), ApiConstants::LOG_LEVEL_DEBUG);

        if ($orderId <= 0 || empty($orderKey)) {
            $this->getLogger()->log('Ошибка: GET-запрос не содержит order_id или order_key. Перенаправляем на главную.');
            if (!headers_sent()) {
                wp_safe_redirect(get_home_url());
                exit;
            }
        }

        $order = wc_get_order($orderId);

        if (!$order || !$order->key_is_valid($orderKey)) {
            $this->getLogger()->log(sprintf('Ошибка: Заказ #%d не найден или ключ невалиден. Перенаправляем на главную.', $orderId));
            if (!headers_sent()) {
                wp_safe_redirect(get_home_url());
                exit;
            }
        }

        // Список статусов, которые означают, что оплата прошла успешно.
        $successful_statuses = ['processing', 'completed'];


        if ( in_array( $order->get_status(), $successful_statuses ) ) {
            $redirectUrl = $order->get_checkout_order_received_url();
            $this->getLogger()->log("Заказ #{$orderId} оплачен. Редирект на страницу 'Спасибо'.");

        } else {
            $redirectUrl = $order->get_view_order_url();
            $this->getLogger()->log("Заказ #{$orderId} еще не оплачен (статус: {$order->get_status()}). Редирект на страницу просмотра заказа.");
        }

        if (headers_sent()) {
            $this->getLogger()->log('КРИТИЧЕСКАЯ ОШИБКА: Заголовки уже были отправлены. Невозможно выполнить редирект.', ApiConstants::LOG_LEVEL_ERROR);
            echo 'Невозможно выполнить редирект. Заголовки уже были отправлены. Пожалуйста, проверьте статус заказа в вашем аккаунте.';
            exit;
        }

        /**
         * Фильтр позволяет изменить URL, на который будет перенаправлен пользователь после возврата из банка.
         *
         * @param string   $redirectUrl URL для перенаправления.
         * @param WC_Order $order       Объект текущего заказа.
         */
        $redirectUrl = apply_filters('tochka_return_url', $redirectUrl, $order);

        wp_safe_redirect($redirectUrl);
        exit;
    }

    /**
     * Генерирует URL для редиректа в AJAX-режиме.
     *
     * @param WC_Order $order Объект заказа.
     * @return string Готовый URL.
     */
    private function getAjaxRedirectUrl(WC_Order $order): string
    {
        $formGenerationUrl = trailingslashit(get_home_url()) . ApiConstants::ENDPOINT_AJAX . '/';

        // Создаем nonce вручную
        $nonce = wp_create_nonce('tochka_pay_nonce');

        // Добавляем все параметры, включая nonce, в массив.
        return add_query_arg([
            'order_id'  => $order->get_id(),
            'order_key' => $order->get_order_key(),
            '_wpnonce'  => $nonce, // Имя параметра должно совпадать с тем, что ищет wp_verify_nonce
        ], $formGenerationUrl);
    }

    /**
     * Ленивая загрузка DTO с настройками.
     *
     * @return Settings
     */
    private function getSettings(): Settings
    {
        if ($this->settingsDto === null) {
            $this->settingsDto = new Settings($this);
        }
        return $this->settingsDto;
    }

    /**
     * Ленивая загрузка логгера.
     *
     * @return Logger
     */
    private function getLogger(): Logger
    {
        if ($this->logger === null) {
            $settings = $this->getSettings();
            $this->logger = new Logger($this->id, $settings->getLoggingLevel());
        }
        return $this->logger;
    }

    /**
     * Валидирует поле "Адрес формы оплаты" при сохранении.
     *
     * @param string $key   Ключ поля.
     * @param string $value Значение поля.
     * @return string
     */
    public function validate_tochka_server_url_field(string $key, string $value): string
    {
        $value = esc_url_raw(trim($value));
        if (empty($value) || !filter_var($value, FILTER_VALIDATE_URL)) {
            WC_Admin_Settings::add_error(esc_html__('Поле "Адрес формы оплаты" не может быть пустым и должно содержать корректный URL.', 'tochka-bank-internet-acquiring'));
        }
        return $value;
    }

    /**
     * Валидирует поле "Логин" при сохранении.
     *
     * @param string $key   Ключ поля.
     * @param string $value Значение поля.
     * @return string
     */
    public function validate_login_field(string $key, string $value): string
    {
        $value = sanitize_text_field(trim($value));
        if (empty($value)) {
            WC_Admin_Settings::add_error(esc_html__('Поле "Логин" не может быть пустым.', 'tochka-bank-internet-acquiring'));
        }
        return $value;
    }

    /**
     * Валидирует поле "Секретное слово" при сохранении.
     *
     * @param string $key   Ключ поля.
     * @param string $value Значение поля.
     * @return string
     */
    public function validate_secret_key_field(string $key, string $value): string
    {
        $value = sanitize_text_field(trim($value));
        if (empty($value)) {
            WC_Admin_Settings::add_error(esc_html__('Поле "Секретное слово" не может быть пустым.', 'tochka-bank-internet-acquiring'));
        }
        return $value;
    }

}