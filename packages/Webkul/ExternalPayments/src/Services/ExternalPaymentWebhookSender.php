<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Webkul\ExternalPayments\Models\ExternalPaymentRequest;
use Webkul\ExternalPayments\Repositories\ExternalPaymentRequestRepository;

class ExternalPaymentWebhookSender
{
    protected Client $client;

    public function __construct(
        protected ExternalPaymentRequestRepository $paymentRequestRepository,
        ?Client $client = null
    ) {
        $this->client = $client ?? new Client(['timeout' => 10, 'connect_timeout' => 5]);
    }

    /**
     * Handle payment success event: find external request and send webhook if URL set.
     */
    public function handlePaymentSuccess(string $paymentProvider, int $providerPaymentId, object $paymentModel): void
    {
        $request = $this->paymentRequestRepository->findByProviderPayment($paymentProvider, $providerPaymentId);

        if (! $request) {
            return;
        }

        if ($request->webhook_sent) {
            Log::info('External Payments: Webhook already sent', [
                'external_payment_request_id' => $request->id,
            ]);
            return;
        }

        $externalSystem = $request->externalSystem;

        if (empty($externalSystem->webhook_url)) {
            Log::info('External Payments: No webhook URL for external system', [
                'external_system_id' => $externalSystem->id,
            ]);
            return;
        }

        $payload = $this->buildPayload($paymentModel, $request);

        try {
            $response = $this->client->post($externalSystem->webhook_url, [
                'json'    => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent'   => 'ExternalPayments-Laravel/1.0',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            $this->paymentRequestRepository->update($request, [
                'webhook_sent'    => true,
                'webhook_sent_at' => now(),
                'status'         => 'paid',
            ]);

            Log::info('External Payments: Webhook sent', [
                'external_payment_request_id' => $request->id,
                'external_system_id'          => $externalSystem->id,
                'status_code'                 => $statusCode,
            ]);
        } catch (GuzzleException $e) {
            Log::error('External Payments: Webhook failed', [
                'external_payment_request_id' => $request->id,
                'error'                       => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildPayload(object $paymentModel, ExternalPaymentRequest $request): array
    {
        $createdAt = isset($paymentModel->created_at) && method_exists($paymentModel->created_at, 'toIso8601String')
            ? $paymentModel->created_at->toIso8601String()
            : (isset($paymentModel->created_at) ? (string) $paymentModel->created_at : '');
        $updatedAt = isset($paymentModel->updated_at) && method_exists($paymentModel->updated_at, 'toIso8601String')
            ? $paymentModel->updated_at->toIso8601String()
            : (isset($paymentModel->updated_at) ? (string) $paymentModel->updated_at : '');

        return [
            'payment_id'         => $paymentModel->id ?? null,
            'order_id'           => $paymentModel->order_id ?? null,
            'external_order_id'  => $paymentModel->external_order_id ?? $request->external_order_id,
            'external_request_id'=> $request->id,
            'transaction_id'     => $paymentModel->transaction_id ?? null,
            'amount'             => isset($paymentModel->amount) ? (float) $paymentModel->amount : 0,
            'status'             => $paymentModel->status ?? 'paid',
            'client'             => [
                'name'  => $paymentModel->client_name ?? null,
                'email' => $paymentModel->client_email ?? null,
                'phone' => $paymentModel->client_phone ?? null,
            ],
            'paid_at'   => $updatedAt,
            'created_at' => $createdAt,
        ];
    }
}
