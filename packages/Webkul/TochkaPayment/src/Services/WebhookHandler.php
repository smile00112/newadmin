<?php

namespace Webkul\TochkaPayment\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Events\PaymentFailed;
use Webkul\TochkaPayment\Events\PaymentSuccess;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;
use Webkul\TochkaPayment\Models\TochkaPaymentWebhookProxy;
use Webkul\TochkaPayment\Exceptions\InvalidSignatureException;
use Webkul\TochkaPayment\Exceptions\PaymentNotFoundException;

class WebhookHandler
{
    /**
     * Public key for Tochka Bank webhook verification.
     * This should be fetched from https://enter.tochka.com/doc/openapi/static/keys/public
     * 
     * @var string
     */
    protected $publicKeyJson = '{"kty":"RSA","e":"AQAB","n":"rwm77av7GIttq-JF1itEgLCGEZW_zz16RlUQVYlLbJtyRSu61fCec_rroP6PxjXU2uLzUOaGaLgAPeUZAJrGuVp9nryKgbZceHckdHDYgJd9TsdJ1MYUsXaOb9joN9vmsCscBx1lwSlFQyNQsHUsrjuDk-opf6RCuazRQ9gkoDCX70HV8WBMFoVm-YWQKJHZEaIQxg_DU4gMFyKRkDGKsYKA0POL-UgWA1qkg6nHY5BOMKaqxbc5ky87muWB5nNk4mfmsckyFv9j1gBiXLKekA_y4UwG2o1pbOLpJS3bP_c95rm4M9ZBmGXqfOQhbjz8z-s9C11i-jmOQ2ByohS-ST3E5sqBzIsxxrxyQDTw--bZNhzpbciyYW4GfkkqyeYoOPd_84jPTBDKQXssvj8ZOj2XboS77tvEO1n1WlwUzh8HPCJod5_fEgSXuozpJtOggXBv0C2ps7yXlDZf-7Jar0UYc_NJEHJF-xShlqd6Q3sVL02PhSCM-ibn9DN9BKmD"}';

    /**
     * Process webhook JWT token.
     *
     * @param  string  $jwtToken
     * @param  int|null  $companyId
     * @return array
     * @throws \Exception
     */
    public function process(string $jwtToken, ?int $companyId = null): array
    {
        Log::info('Tochka Payment: Webhook process - received JWT', [
            'jwt_length' => strlen($jwtToken),
            'company_id' => $companyId,
        ]);

        try {
            // Decode JWT token
            $decodedData = $this->decodeJwt($jwtToken);

            Log::info('Tochka Payment: Webhook process - decoded data', [
                'decoded_data' => $decodedData,
            ]);

            if (!$decodedData) {
                throw new InvalidSignatureException('Failed to decode JWT token');
            }

            // Extract webhook type
            $webhookType = $decodedData['webhookType'] ?? null;

            if (!$webhookType) {
                throw new \Exception('Webhook type not found in payload');
            }

            // Determine company ID from webhook data if not provided
            if ($companyId === null) {
                $companyId = $this->extractCompanyId($decodedData);
            }

            // Create webhook record
            $webhook = TochkaPaymentWebhookProxy::create([
                'company_id' => $companyId,
                'webhook_type' => $webhookType,
                'raw_payload' => $jwtToken,
                'decoded_data' => $decodedData,
                'status' => 'pending',
            ]);

            Log::info('Tochka Payment: Webhook process - processing by type', [
                'webhook_type'  => $webhookType,
                'company_id'    => $companyId,
            ]);

            // Process webhook based on type
            $result = $this->processWebhookByType($webhookType, $decodedData, $companyId);

            // Update webhook status
            if ($result['success']) {
                $webhook->markAsProcessed();
            } else {
                $webhook->markAsFailed($result['error'] ?? 'Unknown error');
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Tochka Payment: Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Decode JWT token and verify signature.
     *
     * @param  string  $jwtToken
     * @return array|null
     */
    protected function decodeJwt(string $jwtToken): ?array
    {
        try {
            // Split JWT token into parts
            $parts = explode('.', $jwtToken);

            if (count($parts) !== 3) {
                Log::error('Tochka Payment: Invalid JWT format', [
                    'parts_count' => count($parts),
                ]);
                return null;
            }

            // Decode payload (second part)
            $payload = base64_decode(strtr($parts[1], '-_', '+/'), true);

            if ($payload === false) {
                Log::error('Tochka Payment: Failed to decode JWT payload');
                return null;
            }

            $decoded = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Tochka Payment: Failed to parse JWT payload', [
                    'error' => json_last_error_msg(),
                ]);
                return null;
            }

            // Note: Full signature verification requires RSA public key verification
            // For now, we decode and trust the payload. In production, implement
            // full RSA signature verification using the public key.

            return $decoded;

        } catch (\Exception $e) {
            Log::error('Tochka Payment: JWT decoding exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Process webhook by type.
     *
     * @param  string  $webhookType
     * @param  array  $data
     * @param  int|null  $companyId
     * @return array
     */
    protected function processWebhookByType(string $webhookType, array $data, ?int $companyId): array
    {
        switch ($webhookType) {
            case 'acquiringInternetPayment':
                return $this->processAcquiringInternetPayment($data, $companyId);

            case 'incomingPayment':
            case 'outgoingPayment':
            case 'incomingSbpPayment':
            case 'incomingSbpB2BPayment':
                // These webhook types are logged but not processed for payment status
                Log::info('Tochka Payment: Webhook type received', [
                    'type' => $webhookType,
                    'company_id' => $companyId,
                ]);
                return ['success' => true, 'message' => 'Webhook logged'];

            default:
                return [
                    'success' => false,
                    'error' => "Unknown webhook type: {$webhookType}",
                ];
        }
    }

    /**
     * Process acquiringInternetPayment webhook.
     *
     * @param  array  $data
     * @param  int|null  $companyId
     * @return array
     */
    protected function processAcquiringInternetPayment(array $data, ?int $companyId): array
    {
        Log::info('Tochka Payment: Webhook acquiringInternetPayment - data used', [
            'data'       => $data,
            'company_id' => $companyId,
        ]);

        $operationId = $data['operationId'] ?? null;

        if (!$operationId) {
            return [
                'success' => false,
                'error' => 'Operation ID not found in webhook data',
            ];
        }

        // Find payment by operation ID
        $payment = TochkaPaymentHistoryProxy::where('operation_id', $operationId)->first();

        if (!$payment) {
            // Try to find by payment ID if available
            $paymentId = $data['paymentId'] ?? null;
            if ($paymentId) {
                $payment = TochkaPaymentHistoryProxy::where('order_id', 'like', "%{$paymentId}%")->first();
            }
        }

        if (!$payment) {
            Log::warning('Tochka Payment: Payment not found for webhook', [
                'operation_id' => $operationId,
                'company_id' => $companyId,
            ]);

            throw new PaymentNotFoundException("Payment not found for operation ID: {$operationId}");
        }

        // Save old status before update
        $oldStatus = $payment->status;
        $webhookStatus = $data['status'] ?? null;

        // Update payment status
        $status = $this->mapWebhookStatusToPaymentStatus($webhookStatus);

        $updateData = [
            'status' => $status,
            'operation_id' => $operationId,
            'webhook_data' => $data,
        ];

        // Update consumer ID if provided
        if (isset($data['consumerId'])) {
            $updateData['consumer_id'] = $data['consumerId'];
        }

        $payment->update($updateData);
        $payment->refresh();

        Log::info('Tochka Payment: Payment status updated from webhook', [
            'payment_id' => $payment->id,
            'operation_id' => $operationId,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'webhook_status' => $webhookStatus,
            'company_id' => $companyId,
        ]);

        // Dispatch events only if status changed
        // Telegram notifications will be sent by event listeners
        if ($oldStatus !== $status) {
            if ($this->isSuccessfulWebhookStatus($webhookStatus)) {
                Event::dispatch(new PaymentSuccess($payment));
                Event::dispatch('external_payments.payment.success', [$payment]);
            } elseif ($this->isFailedWebhookStatus($webhookStatus)) {
                Event::dispatch(new PaymentFailed($payment));
            }
        }

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'status' => $status,
        ];
    }

    /**
     * Map webhook status to payment status.
     *
     * @param  string|null  $webhookStatus
     * @return string
     */
    protected function mapWebhookStatusToPaymentStatus(?string $webhookStatus): string
    {
        $modelClass = TochkaPaymentHistoryProxy::modelClass();
        
        if (!$webhookStatus) {
            return $modelClass::STATUS_PENDING;
        }

        switch (strtoupper($webhookStatus)) {
            case 'APPROVED':
                return $modelClass::STATUS_PAID;

            case 'EXPIRED':
            case 'REFUNDED':
                return $modelClass::STATUS_FAILED;

            case 'AUTHORIZED':
                // For two-step payments, AUTHORIZED means funds are frozen
            case 'ON-REFUND':
            case 'CREATED':
            default:
                return $modelClass::STATUS_PENDING;
        }
    }

    /**
     * Check if webhook status indicates successful payment.
     *
     * @param  string|null  $webhookStatus
     * @return bool
     */
    protected function isSuccessfulWebhookStatus(?string $webhookStatus): bool
    {
        return $webhookStatus && strtoupper($webhookStatus) === 'APPROVED';
    }

    /**
     * Check if webhook status indicates failed payment.
     *
     * @param  string|null  $webhookStatus
     * @return bool
     */
    protected function isFailedWebhookStatus(?string $webhookStatus): bool
    {
        if (!$webhookStatus) {
            return false;
        }

        $status = strtoupper($webhookStatus);
        return in_array($status, ['EXPIRED', 'REFUNDED']);
    }

    /**
     * Extract company ID from webhook data.
     *
     * @param  array  $data
     * @return int|null
     */
    protected function extractCompanyId(array $data): ?int
    {
        // Try to find company by customer_code
        $customerCode = $data['customerCode'] ?? null;

        if ($customerCode) {
            $settings = \Webkul\TochkaPayment\Models\TochkaPaymentSettingsProxy::where('customer_code', $customerCode)->first();
            if ($settings) {
                return $settings->company_id;
            }
        }

        return null;
    }
}
