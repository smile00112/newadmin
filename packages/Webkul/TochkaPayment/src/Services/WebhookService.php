<?php

namespace Webkul\TochkaPayment\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;

class WebhookService
{
    /**
     * HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Create a new webhook service instance.
     */
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * Send webhook notification about successful payment.
     *
     * @param  \Webkul\TochkaPayment\Models\TochkaPaymentHistory  $payment
     * @return bool
     */
    public function sendPaymentNotification($payment): bool
    {
        $webhookUrl = config('tochka-payment.webhook_url');

        if (empty($webhookUrl)) {
            Log::warning('Tochka Payment: Webhook URL not configured', ['payment_id' => $payment->id]);
            return false;
        }

        if ($payment->webhook_sent) {
            Log::info('Tochka Payment: Webhook already sent', ['payment_id' => $payment->id]);
            return true;
        }

        $payload = $this->buildPayload($payment);

        try {
            $response = $this->client->post($webhookUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'TochkaPayment-Laravel/1.0',
                ],
            ]);

            $responseBody = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();

            // Update payment record
            $payment->update([
                'webhook_sent' => true,
                'webhook_response' => $responseBody,
                'webhook_attempts' => $payment->webhook_attempts + 1,
            ]);

            Log::info('Tochka Payment: Webhook sent successfully', [
                'payment_id' => $payment->id,
                'status_code' => $statusCode,
            ]);

            return $statusCode >= 200 && $statusCode < 300;
        } catch (GuzzleException $e) {
            // Update payment record with error
            $payment->update([
                'webhook_response' => $e->getMessage(),
                'webhook_attempts' => $payment->webhook_attempts + 1,
            ]);

            Log::error('Tochka Payment: Webhook sending failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Build webhook payload.
     *
     * @param  \Webkul\TochkaPayment\Models\TochkaPaymentHistory  $payment
     * @return array
     */
    protected function buildPayload($payment): array
    {
        return [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'external_order_id' => $payment->external_order_id,
            'transaction_id' => $payment->transaction_id,
            'amount' => (float) $payment->amount,
            'status' => $payment->status,
            'client' => [
                'name' => $payment->client_name,
                'email' => $payment->client_email,
                'phone' => $payment->client_phone,
            ],
            'paid_at' => $payment->updated_at->toIso8601String(),
            'created_at' => $payment->created_at->toIso8601String(),
        ];
    }

    /**
     * Retry sending webhook (for failed attempts).
     *
     * @param  \Webkul\TochkaPayment\Models\TochkaPaymentHistory  $payment
     * @param  int  $maxAttempts
     * @return bool
     */
    public function retryWebhook($payment, int $maxAttempts = 3): bool
    {
        if ($payment->webhook_attempts >= $maxAttempts) {
            Log::warning('Tochka Payment: Max webhook attempts reached', [
                'payment_id' => $payment->id,
                'attempts' => $payment->webhook_attempts,
            ]);
            return false;
        }

        return $this->sendPaymentNotification($payment);
    }
}
