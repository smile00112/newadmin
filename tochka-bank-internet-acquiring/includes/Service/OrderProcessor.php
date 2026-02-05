<?php
// Файл: includes/Service/OrderProcessor.php

declare(strict_types=1);

namespace Tochka\Woocommerce\Service;

use Exception;
use WC_Order;
use Tochka\Woocommerce\Bootstrap;

/**
 * Класс, отвечающий за обработку заказа после получения успешного уведомления об оплате.
 */
final class OrderProcessor
{
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
     * @param Settings $settings Объект с настройками шлюза.
     * @param Logger   $logger   Экземпляр логгера.
     */
    public function __construct(Settings $settings, Logger $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Выполняет полную обработку успешного платежа для заказа.
     *
     * @param array{order_id: int, transaction_id: string, amount: float} $paymentResult Валидированные данные из CallbackHandler.
     *
     * @return WC_Order Объект обработанного заказа.
     * @throws Exception Если заказ не найден, имеет неверный статус, сумму или метод оплаты.
     */
    public function processSuccessfulPayment(array $paymentResult): WC_Order
    {
        $orderId = $paymentResult['order_id'];
        $order = wc_get_order($orderId);

        // 1. Проверка существования заказа
        if (!$order) {
            $this->logger->log(sprintf('ОШИБКА: Заказ #%d не найден в системе при обработке.', $orderId), ApiConstants::LOG_LEVEL_ERROR);
            throw new Exception('Error! Order not found.', 404);
        }

        // 2. Проверка метода оплаты
        if ($order->get_payment_method() !== Bootstrap::GATEWAY_ID) {
            $this->logger->log(sprintf('ОШИБКА: Коллбэк для заказа #%d пришел, но способ оплаты не совпадает.', $orderId), ApiConstants::LOG_LEVEL_ERROR);
            throw new Exception('Invalid payment method for this callback.', 400);
        }

        // 3. Проверка, не был ли заказ уже оплачен
        if ($order->is_paid()) {
            $this->logger->log(sprintf('Заказ #%d уже оплачен. Повторная обработка callback пропущена.', $orderId));
            return $order;
        }

        // 4. Проверка суммы платежа
        if (abs($order->get_total() - $paymentResult['amount']) > 0.01) {
            $this->logger->log(sprintf('ОШИБКА: Неверная сумма для заказа #%d. Ожидали: %s, Получили: %s.', $orderId, $order->get_total(), $paymentResult['amount']), ApiConstants::LOG_LEVEL_ERROR);
            throw new Exception('Error! Incorrect order sum.', 400);
        }

        $this->logger->log(sprintf('Все проверки для заказа #%d пройдены. Завершение оплаты.', $orderId));

        // 5. Завершение оплаты
        $order->add_order_note(
            /* translators: %s: Transaction ID from the bank. */
            sprintf(__('Оплата успешно получена через "Точка". ID транзакции: %s', 'tochka-bank-internet-acquiring'), $paymentResult['transaction_id'])
        );
        $order->payment_complete($paymentResult['transaction_id']);

        // 6. Обновление статуса заказа
        $this->updateOrderStatus($order);

        /**
         * Действие (action) выполняется после успешной обработки платежа.
         * Позволяет другим плагинам и темам выполнять свои действия.
         *
         * @param WC_Order $order         Объект заказа.
         * @param array    $paymentResult Данные, полученные от платежной системы.
         */
        do_action('tochka_payment_successful', $order, $paymentResult);

        return $order;
    }

    /**
     * Обновляет статус заказа до целевого, указанного в настройках.
     *
     * @param WC_Order $order Объект заказа.
     */
    private function updateOrderStatus(WC_Order $order): void
    {
        $targetStatus = $this->settings->getCompletedOrderStatus();

        if (empty($targetStatus)) {
            return;
        }

        $targetStatusSlug = str_replace('wc-', '', $targetStatus);

        if ($order->get_status() !== $targetStatusSlug) {
            $this->logger->log(sprintf('Изменение статуса заказа #%d на "%s" согласно настройкам.', $order->get_id(), $targetStatusSlug));
            $order->update_status($targetStatus, __('Статус заказа обновлен согласно настройкам шлюза.', 'tochka-bank-internet-acquiring'));
        }
    }
}