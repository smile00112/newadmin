<?php

namespace Webkul\AlfabankPayment\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\AlfabankPayment\Services\AlfabankApiService;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\RefundRepository;

class RefundOrderListener
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        protected AlfabankApiService $apiService,
        protected OrderRepository $orderRepository,
        protected RefundRepository $refundRepository
    ) {}

    /**
     * Handle sales refund creation before transaction is saved.
     *
     * @param  mixed  $payload
     * @return void
     */
    public function handle(mixed $payload): void
    {
        $data = $this->normalizePayload($payload);

        $orderId = (int) ($data['order_id'] ?? 0);

        if ($orderId <= 0) {
            return;
        }

        $order = $this->orderRepository->find($orderId);

        if (! $order || $order->payment?->method !== 'alfabank') {
            return;
        }

        $bankOrderId = $this->resolveBankOrderId($order);

        if (! $bankOrderId) {
            throw new \RuntimeException('Alfabank refund error: bank order id is missing for this order.');
        }

        $refundInput = $data['refund'] ?? [];
        $totals = $this->refundRepository->getOrderItemsRefundSummary($refundInput, $order->id);
        $baseAmount = (float) ($totals['grand_total']['price'] ?? 0);

        $amountInOrderCurrency = (float) core()->convertPrice($baseAmount, $order->order_currency_code);
        $amount = (int) round($amountInOrderCurrency * 100);

        if ($amount <= 0) {
            throw new \RuntimeException('Alfabank refund error: refund amount must be greater than zero.');
        }

        $currency = $this->resolveNumericCurrencyCode((string) $order->order_currency_code);
        $language = app()->getLocale() ?: 'ru';

        $response = $this->apiService->refundOrder($bankOrderId, $amount, $language, $currency);
        $errorCode = (string) ($response['errorCode'] ?? '');

        if ($errorCode !== '' && $errorCode !== '0') {
            Log::error('Alfabank refund request failed', [
                'order_id'       => $order->id,
                'bank_order_id'  => $bankOrderId,
                'amount'         => $amount,
                'response'       => $response,
            ]);

            throw new \RuntimeException(
                'Alfabank refund error: ' . ($response['errorMessage'] ?? 'Unknown gateway error')
            );
        }
    }

    /**
     * Normalize event payload from Laravel event dispatcher.
     *
     * @param  mixed  $payload
     * @return array<string, mixed>
     */
    protected function normalizePayload(mixed $payload): array
    {
        if (is_array($payload) && array_key_exists('order_id', $payload)) {
            return $payload;
        }

        if (is_array($payload) && isset($payload[0]) && is_array($payload[0])) {
            return $payload[0];
        }

        return [];
    }

    /**
     * Resolve bank order id from order payment additional data.
     */
    protected function resolveBankOrderId($order): ?string
    {
        $paymentAdditional = $order->payment?->additional ?? [];
        $orderAdditional = $order->additional ?? [];

        $bankOrderId = $paymentAdditional['alfabank_order_id']
            ?? $orderAdditional['alfabank_order_id']
            ?? null;

        return is_string($bankOrderId) && $bankOrderId !== '' ? $bankOrderId : null;
    }

    /**
     * Resolve numeric ISO 4217 currency code by alpha code.
     */
    protected function resolveNumericCurrencyCode(string $currencyCode): ?string
    {
        $codes = [
            'BYN' => '933',
            'BYR' => '974',
            'RUB' => '643',
            'USD' => '840',
            'EUR' => '978',
        ];

        return $codes[strtoupper($currencyCode)] ?? null;
    }
}
