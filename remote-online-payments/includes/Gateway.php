<?php
// Файл: includes/Gateway.php

declare(strict_types=1);

namespace RemoteOnlinePayments;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use WC_Order;
use WC_Payment_Gateway;
use RemoteOnlinePayments\Admin\SettingsForm;
use RemoteOnlinePayments\Service\ApiClient;
use RemoteOnlinePayments\Service\ApiConstants;
use RemoteOnlinePayments\Service\CallbackHandler;
use RemoteOnlinePayments\Service\Logger;
use RemoteOnlinePayments\Service\OrderProcessor;
use RemoteOnlinePayments\Service\Settings;
use RemoteOnlinePayments\Exception\CallbackException;

/**
 * Основной класс платежного шлюза.
 */
final class Gateway extends WC_Payment_Gateway
{
    /**
     * @var Settings|null Кэшированный DTO с настройками
     */
    private ?Settings $settingsDto = null;

    /**
     * @var Logger|null Кэшированный экземпляр логгера
     */
    private ?Logger $logger = null;

    /**
     * Конструктор класса.
     * Инициализирует основные свойства шлюза и регистрирует необходимые хуки.
     */
    public function __construct()
    {
        $this->id = Bootstrap::GATEWAY_ID;
        $this->method_title = __('Онлайн оплата', 'remote-online-payments');
        $this->method_description = __('Платежный шлюз для проведения онлайн оплаты на стороннем сервере.', 'remote-online-payments');
        $this->has_fields = false;
        $this->supports = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title', __('Онлайн оплата', 'remote-online-payments'));
        $this->description = $this->get_option('description', __('Оплата через сторонний сервер', 'remote-online-payments'));
        $this->enabled = $this->get_option('enabled');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_api_' . strtolower($this->id), [$this, 'handle_callback']);
    }

    /**
     * Инициализирует поля формы настроек для админ-панели WooCommerce.
     */
    public function init_form_fields(): void
    {
        $this->form_fields = SettingsForm::get_fields();
    }

    /**
     * Обрабатывает платеж и возвращает результат для WooCommerce.
     *
     * @param int $order_id ID заказа
     * @return array{result: string, redirect: string}
     * @throws Exception В случае ошибки, которая будет показана пользователю
     */
    public function process_payment($order_id): array
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception(__('Заказ не найден.', 'remote-online-payments'));
        }

        $this->getLogger()->log(
            sprintf('Начало обработки платежа для заказа #%d.', $order_id),
            ApiConstants::LOG_LEVEL_INFO
        );

        try {
            // Создаем клиент API и отправляем запрос на создание платежа
            $apiClient = new ApiClient($this->getSettings(), $this->getLogger());
            $paymentUrl = $apiClient->createPayment($order);

            // Сохраняем ссылку на оплату в мета-поле заказа
            $order->update_meta_data(ApiConstants::META_PAYMENT_URL, $paymentUrl);
            $order->save_meta_data();

            $this->getLogger()->log(
                sprintf('Ссылка на оплату сохранена для заказа #%d', $order_id),
                ApiConstants::LOG_LEVEL_INFO
            );

            // Возвращаем успешный результат с редиректом на страницу оплаты
            return [
                'result' => 'success',
                'redirect' => $paymentUrl,
            ];
        } catch (Exception $e) {
            $this->getLogger()->log(
                sprintf('ОШИБКА при обработке платежа для заказа #%d: %s', $order_id, $e->getMessage()),
                ApiConstants::LOG_LEVEL_ERROR
            );

            // Добавляем заметку к заказу об ошибке
            $order->add_order_note(
                sprintf(__('Ошибка при создании платежа: %s', 'remote-online-payments'), $e->getMessage())
            );

            throw new Exception(
                __('Не удалось создать платеж. Пожалуйста, попробуйте еще раз или выберите другой способ оплаты.', 'remote-online-payments')
            );
        }
    }

    /**
     * Обрабатывает входящие запросы от стороннего сервера (callback).
     * Вызывается через хук `woocommerce_api_{$this->id}`.
     */
    #[NoReturn]
    public function handle_callback(): void
    {
        // Получаем данные из тела запроса
        $rawBody = file_get_contents('php://input');
        $requestData = json_decode($rawBody, true);

        // Если JSON не удалось декодировать, пробуем получить из $_POST
        if (json_last_error() !== JSON_ERROR_NONE || empty($requestData)) {
            $requestData = $_POST ?? [];
        }

        $handler = new CallbackHandler($requestData, $this->getSettings(), $this->getLogger());

        try {
            // Валидируем входящий запрос
            $callbackData = $handler->process();

            // Создаем обработчик заказа и передаем ему данные
            $orderProcessor = new OrderProcessor($this->getSettings(), $this->getLogger());
            $orderProcessor->processPaymentCallback($callbackData);

            // Отправляем успешный ответ серверу
            header('Content-Type: application/json');
            echo $handler->getSuccessResponse();
            exit;
        } catch (CallbackException $e) {
            // Ошибки валидации callback
            $this->getLogger()->log($e->getMessage(), ApiConstants::LOG_LEVEL_ERROR);
            wp_die($e->getMessage(), __('Callback Error', 'remote-online-payments'), ['response' => $e->getHttpStatusCode()]);
        } catch (Exception $e) {
            // Ошибки обработки заказа
            $this->getLogger()->log(
                sprintf('Ошибка обработки заказа: %s', $e->getMessage()),
                ApiConstants::LOG_LEVEL_ERROR
            );
            wp_die(
                $e->getMessage(),
                __('Order Processing Error', 'remote-online-payments'),
                ['response' => $e->getCode() ?: 400]
            );
        }
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
}
