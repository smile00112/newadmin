<?php

namespace Webkul\TochkaPayment\Services;

use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;

class PaymentRequestBuilder
{
    /**
     * Build payment request parameters for Tochka Bank.
     *
     * @param  array  $data
     * @param  int  $paymentId
     * @return array
     */
    public function buildRequestParams(array $data, int $paymentId): array
    {
        $orderId = $paymentId . '|' . time();
        $amount = number_format((float) $data['amount'], 2, '.', '');
        $clientPhone = preg_replace('/[^0-9+]/', '', $data['client_phone']);

        $formData = [
            'sum' => $amount,
            'orderid' => $orderId,
            'clientid' => trim($data['client_name']),
            'client_email' => $data['client_email'],
            'client_phone' => $clientPhone,
            'login' => config('tochka-payment.login'),
            'service_name' => config('tochka-payment.service_name'),
            'lang' => config('tochka-payment.lang', 'ru'),
            'callback_url' => $this->getCallbackUrl(),
        ];

        // Calculate signature
        $formData['sign'] = $this->calculateSignature($formData);

        return $formData;
    }

    /**
     * Calculate SHA256 signature for payment request.
     *
     * @param  array  $formData
     * @return string
     */
    public function calculateSignature(array $formData): string
    {
        $stringToHash = $formData['sum'] .
            $formData['clientid'] .
            $formData['orderid'] .
            $formData['service_name'] .
            $formData['client_email'] .
            $formData['client_phone'] .
            config('tochka-payment.login') .
            config('tochka-payment.secret_key');

        return hash(config('tochka-payment.signature_algorithms.request'), $stringToHash);
    }

    /**
     * Get callback URL for payment notifications.
     *
     * @return string
     */
    public function getCallbackUrl(): string
    {
        return route('tochka-payment.callback');
    }

    /**
     * Build payment URL with form data.
     *
     * @param  array  $formData
     * @return string
     */
    public function buildPaymentUrl(array $formData): string
    {
        $serverUrl = config('tochka-payment.server_url');

        // Build query string
        $queryParams = http_build_query($formData);

        return $serverUrl . '?' . $queryParams;
    }

    /**
     * Create payment history record.
     *
     * @param  array  $data
     * @param  array  $requestParams
     * @param  string  $paymentUrl
     * @return \Webkul\TochkaPayment\Models\TochkaPaymentHistory
     */
    public function createPaymentHistory(array $data, array $requestParams, string $paymentUrl)
    {
        // Extract order_id from request params if available
        $orderId = $requestParams['orderid'] ?? '';

        return TochkaPaymentHistoryProxy::create([
            'external_order_id' => $data['external_order_id'] ?? null,
            'order_id' => $orderId,
            'amount' => $data['amount'],
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'],
            'client_phone' => $data['client_phone'],
            'payment_url' => $paymentUrl,
            'status' => 'pending',
            'request_data' => $requestParams ?: null,
        ]);
    }

    /**
     * Mask sensitive data for logging.
     *
     * @param  array  $data
     * @return array
     */
    public function maskSensitiveData(array $data): array
    {
        $masked = $data;

        if (isset($masked['login'])) {
            $masked['login'] = '****';
        }

        if (isset($masked['sign'])) {
            $masked['sign'] = substr($masked['sign'], 0, 8) . '...';
        }

        return $masked;
    }
}
