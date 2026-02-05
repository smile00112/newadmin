<?php
// Файл: includes/Service/OrderProcessor.php

declare(strict_types=1);

namespace RemoteOnlinePayments\Service;

use Exception;
use WC_Order;
use RemoteOnlinePayments\Bootstrap;

/**
 * Класс, отвечающий за обработку заказа после получения уведомления об оплате.
 */
final class OrderProcessor
{
    /**
     * @var Settings DTO с настройками шлюза
     */
    private Settings $settings;

    /**
     * @var Logger Экземпляр логгера
     */
    private Logger $logger;

    /**
     * Конструктор.
     *
     * @param Settings $settings Объект с настройками шлюза
     * @param Logger $logger Экземпляр логгера
     */
    public function __construct(Settings $settings, Logger $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Выполняет обработку уведомления об оплате.
     *
     * @param array{order_id: int, status: string, transaction_id: string|null, amount: float|null, message: string|null} $callbackData
     * @return WC_Order Объект обработанного заказа
     * @throws Exception Если заказ не найден или произошла ошибка обработки
     */
    public function processPaymentCallback(array $callbackData): WC_Order
    {
        $orderId = $callbackData['order_id'];
        $status = $callbackData['status'];
        $order = wc_get_order($orderId);

        // Проверка существования заказа
        if (!$order) {
            $this->logger->log(
                sprintf('ОШИБКА: Заказ #%d не найден в системе при обработке callback.', $orderId),
                ApiConstants::LOG_LEVEL_ERROR
            );
            throw new Exception(__('Заказ не найден.', 'remote-online-payments'), 404);
        }

        // Проверка метода оплаты
        if ($order->get_payment_method() !== Bootstrap::GATEWAY_ID) {
            $this->logger->log(
                sprintf('ОШИБКА: Callback для заказа #%d пришел, но способ оплаты не совпадает.', $orderId),
                ApiConstants::LOG_LEVEL_ERROR
            );
            throw new Exception(__('Неверный метод оплаты для этого callback.', 'remote-online-payments'), 400);
        }

        // Обработка в зависимости от статуса
        if ($status === ApiConstants::STATUS_SUCCESS) {
            return $this->processSuccessfulPayment($order, $callbackData);
        } elseif ($status === ApiConstants::STATUS_FAILED) {
            return $this->processFailedPayment($order, $callbackData);
        } else {
            // Статус pending или другой - просто логируем
            $this->logger->log(
                sprintf('Получен callback со статусом "%s" для заказа #%d. Заказ остается в текущем статусе.', $status, $orderId),
                ApiConstants::LOG_LEVEL_INFO
            );
            return $order;
        }
    }

    /**
     * Обрабатывает успешную оплату.
     *
     * @param WC_Order $order Объект заказа
     * @param array $callbackData Данные callback
     * @return WC_Order Объект заказа
     * @throws Exception Если произошла ошибка
     */
    private function processSuccessfulPayment(WC_Order $order, array $callbackData): WC_Order
    {
        $orderId = $order->get_id();

        // Проверка, не был ли заказ уже оплачен
        if ($order->is_paid()) {
            $this->logger->log(
                sprintf('Заказ #%d уже оплачен. Повторная обработка callback пропущена.', $orderId),
                ApiConstants::LOG_LEVEL_INFO
            );
            return $order;
        }

        // Проверка суммы платежа, если она указана
        if (isset($callbackData['amount']) && $callbackData['amount'] > 0) {
            $orderTotal = (float) $order->get_total();
            $paymentAmount = (float) $callbackData['amount'];
            
            if (abs($orderTotal - $paymentAmount) > 0.01) {
                $this->logger->log(
                    sprintf(
                        'ОШИБКА: Неверная сумма для заказа #%d. Ожидали: %s, Получили: %s.',
                        $orderId,
                        $orderTotal,
                        $paymentAmount
                    ),
                    ApiConstants::LOG_LEVEL_ERROR
                );
                throw new Exception(__('Неверная сумма заказа.', 'remote-online-payments'), 400);
            }
        }

        $this->logger->log(
            sprintf('Все проверки для заказа #%d пройдены. Завершение оплаты.', $orderId),
            ApiConstants::LOG_LEVEL_INFO
        );

        // Сохранение ID транзакции, если он указан
        if (!empty($callbackData['transaction_id'])) {
            $order->update_meta_data(ApiConstants::META_TRANSACTION_ID, $callbackData['transaction_id']);
        }

        // Добавление заметки к заказу
        $note = __('Оплата успешно получена через удаленный сервер.', 'remote-online-payments');
        if (!empty($callbackData['transaction_id'])) {
            $note .= ' ' . sprintf(__('ID транзакции: %s', 'remote-online-payments'), $callbackData['transaction_id']);
        }
        if (!empty($callbackData['message'])) {
            $note .= ' ' . $callbackData['message'];
        }
        $order->add_order_note($note);

        // Завершение оплаты
        $transactionId = $callbackData['transaction_id'] ?? '';
        $order->payment_complete($transactionId);

        // Обновление статуса заказа
        $this->updateOrderStatus($order);

        /**
         * Действие выполняется после успешной обработки платежа.
         *
         * @param WC_Order $order Объект заказа
         * @param array $callbackData Данные, полученные от платежной системы
         */
        do_action('remote_online_payments_payment_successful', $order, $callbackData);

        return $order;
    }

    /**
     * Обрабатывает неуспешную оплату.
     *
     * @param WC_Order $order Объект заказа
     * @param array $callbackData Данные callback
     * @return WC_Order Объект заказа
     */
    private function processFailedPayment(WC_Order $order, array $callbackData): WC_Order
    {
        $orderId = $order->get_id();

        $this->logger->log(
            sprintf('Получено уведомление о неуспешной оплате для заказа #%d', $orderId),
            ApiConstants::LOG_LEVEL_INFO
        );

        // Добавление заметки к заказу
        $note = __('Оплата не была завершена через удаленный сервер.', 'remote-online-payments');
        if (!empty($callbackData['message'])) {
            $note .= ' ' . $callbackData['message'];
        }
        $order->add_order_note($note);

        // Обновление статуса на "failed", если заказ еще не оплачен
        if (!$order->is_paid()) {
            $order->update_status('failed', __('Оплата не была завершена.', 'remote-online-payments'));
        }

        /**
         * Действие выполняется после обработки неуспешного платежа.
         *
         * @param WC_Order $order Объект заказа
         * @param array $callbackData Данные, полученные от платежной системы
         */
        do_action('remote_online_payments_payment_failed', $order, $callbackData);

        return $order;
    }

    /**
     * Обновляет статус заказа до целевого, указанного в настройках.
     *
     * @param WC_Order $order Объект заказа
     */
    private function updateOrderStatus(WC_Order $order): void
    {
        $targetStatus = $this->settings->getCompletedOrderStatus();

        if (empty($targetStatus)) {
            return;
        }

        $targetStatusSlug = str_replace('wc-', '', $targetStatus);

        if ($order->get_status() !== $targetStatusSlug) {
            $this->logger->log(
                sprintf('Изменение статуса заказа #%d на "%s" согласно настройкам.', $order->get_id(), $targetStatusSlug),
                ApiConstants::LOG_LEVEL_INFO
            );
            $order->update_status(
                $targetStatus,
                __('Статус заказа обновлен согласно настройкам шлюза.', 'remote-online-payments')
            );
        }
    }
}
