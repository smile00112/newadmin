<?php
// Файл: includes/Service/RequestBuilder.php

declare(strict_types=1);

namespace Tochka\Woocommerce\Service;

use Tochka\Woocommerce\CartBuilder;
use WC_Order;
use Exception;

/**
 * Формирует массив параметров для запроса на создание платежа.
 * Реализует логику сбора данных о клиенте, заказе,
 * а также вычисление контрольной подписи.
 */
final class RequestBuilder
{
    /**
     * @var WC_Order Объект заказа WooCommerce.
     */
    private WC_Order $order;

    /**
     * @var Settings DTO с настройками шлюза.
     */
    private Settings $settings;

    /**
     * @var Logger Экземпляр логгера.
     */
    private Logger $logger;

    /**
     * Конструктор.
     *
     * @param WC_Order  $order    Объект заказа.
     * @param Settings  $settings Объект с настройками шлюза.
     * @param Logger    $logger   Экземпляр логгера.
     */
    public function __construct(WC_Order $order, Settings $settings, Logger $logger)
    {
        $this->order = $order;
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Возвращает полный массив параметров для HTML-формы оплаты.
     *
     * @return array<string, string|mixed> Возвращает ассоциативный массив параметров:
     *  - sum: string (сумма заказа, формат 0.00)
     *  - orderid: string (ID заказа + timestamp)
     *  - clientid: string (Имя клиента)
     *  - client_email: string
     *  - client_phone: string
     *  - login: string (ID магазина)
     *  - service_name: string
     *  - lang: string
     *  - callback_url: string
     *  - sign: string (SHA256 подпись)
     *  - cart?: string (JSON с корзиной, опционально)
     */
    public function getParams(): array
    {
        $uniqueOrderId = $this->order->get_id() . '|' . time();
        $totalAmount = $this->order->get_total();

        $form_data = [
            'sum'          => number_format((float)$totalAmount, 2, '.', ''),
            'orderid'      => $uniqueOrderId,
            'clientid'     => trim($this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name()),
            'client_email' => $this->order->get_billing_email(),
            'client_phone' => preg_replace('/[^0-9+]/', '', $this->order->get_billing_phone()),
            'login'        => $this->settings->getLogin(),
            'service_name' => ApiConstants::SERVICE_NAME,
            'lang'         => substr(get_locale(), 0, 2),
            'callback_url' => $this->getSmartUrl(),
        ];

        if ($this->settings->isCartEnable()) {
            try {
                $cartBuilder = new CartBuilder($this->order, $this->settings->getRawSettingsArray());
                $cartJson = json_encode($cartBuilder->build(), JSON_UNESCAPED_UNICODE);
                if ($cartJson) {
                    $form_data['cart'] = $cartJson;
                }
            } catch (Exception $e) {
                $this->logger->log(
                    'Ошибка при сборке фискальной корзины: ' . $e->getMessage(),
                    ApiConstants::LOG_LEVEL_ERROR
                );
            }
        }

        /**
         * Фильтр позволяет модифицировать массив данных перед вычислением подписи и отправкой в банк.
         *
         * @param array<string, mixed>     $form_data Массив с параметрами запроса.
         * @param WC_Order                 $order     Объект текущего заказа.
         */
        $form_data = apply_filters('tochka_before_payment_request', $form_data, $this->order);

        // Пересчитываем подпись ПОСЛЕ применения фильтров, на случай если были изменены ключевые поля.
        $form_data['sign'] = $this->calculateSignature($form_data);

        $this->logger->log(
            'Сгенерированы параметры для формы оплаты',
            ApiConstants::LOG_LEVEL_DEBUG,
            $this->maskArray($form_data)
        );

        return $form_data;
    }

    /**
     * Генерирует единый "умный" URL для callback и возврата пользователя.
     *
     * @return string
     */
    private function getSmartUrl(): string
    {
        $apiUrl = WC()->api_request_url($this->settings->getId());

        return add_query_arg([
            'order_id'  => $this->order->get_id(),
            'order_key' => $this->order->get_order_key(),
        ], $apiUrl);
    }

    /**
     * Вычисляет контрольную подпись для формы.
     *
     * @param array<string, mixed> $formData Данные формы.
     * @return string Хеш.
     */
    private function calculateSignature(array $formData): string
    {
        $stringToHash = $formData['sum'] .
            $formData['clientid'] .
            $formData['orderid'] .
            $formData['service_name'] .
            $formData['client_email'] .
            $formData['client_phone'] .
            $this->settings->getLogin() .
            $this->settings->getSecretKey();

        return hash(ApiConstants::REQUEST_SIGNATURE_ALGO, $stringToHash);
    }

    /**
     * Маскирует чувствительные данные в массиве для безопасного логирования.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    private function maskArray(array $array): array
    {
        if (isset($array['login'])) {
            $array['login'] = '****';
        }
        if (isset($array['sign'])) {
            $array['sign'] = substr($array['sign'], 0, 8) . '...';
        }
        return $array;
    }
}