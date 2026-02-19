<?php

namespace Webkul\TochkaPayment\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class PaymentStatusService
{
    /**
     * HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Settings service instance.
     *
     * @var \Webkul\TochkaPayment\Services\SettingsService
     */
    protected $settingsService;

    /**
     * Create a new payment status service instance.
     */
    public function __construct(?SettingsService $settingsService = null)
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
        $this->settingsService = $settingsService ?? new SettingsService();
    }

    /**
     * Get payment operation status from Tochka Bank API.
     *
     * @param  string  $operationId
     * @param  int|null  $companyId
     * @return array Returns array with 'status', 'operationId', and full response data
     * @throws \Exception
     */
    public function getOperationStatus(string $operationId, ?int $companyId = null): array
    {
        // Get settings for company
        $settings = $this->settingsService->getSettings($companyId);

        $apiBaseUrl = $settings['api_base_url'];
        $bearerToken = $settings['jwt_token'];

        if (empty($apiBaseUrl)) {
            throw new \Exception('Tochka API base URL is not configured');
        }

        if (empty($bearerToken)) {
            throw new \Exception('Tochka JWT token is not configured');
        }

        $endpoint = rtrim($apiBaseUrl, '/') . '/acquiring/v1.0/payments/' . $operationId;

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
        ];

        Log::info('Tochka Payment: Request operation status', [
            'url' => $endpoint,
            'operation_id' => $operationId,
            'headers' => $this->getHeadersForLog($requestHeaders),
        ]);

        try {
            $response = $this->client->get($endpoint, [
                'headers' => $requestHeaders,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Tochka API: ' . json_last_error_msg());
            }

            Log::info('Tochka Payment: Operation status response', [
                'url' => $endpoint,
                'status_code' => $statusCode,
                'operation_id' => $operationId,
                'response_body' => $this->maskSensitiveData(is_array($responseData) ? $responseData : ['raw' => $responseBody]),
            ]);

            // Extract status from response
            // Status can be in different places depending on API response structure
            $operation = $responseData['Data']['Operation'][0] ?? null;
            $status = $responseData['status']
                ?? $responseData['Data']['status']
                ?? ($operation['status'] ?? null)
                ?? $responseData['operationStatus']
                ?? $responseData['Data']['operationStatus']
                ?? null;

            if ($status === null) {
                Log::warning('Tochka Payment: Status not found in response', [
                    'response' => $responseData,
                    'operation_id' => $operationId,
                ]);
                throw new \Exception('Status not found in API response');
            }

            return [
                'status' => $status,
                'operation_id' => $operationId,
                'response_data' => $responseData,
            ];

        } catch (GuzzleException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            $errorData = is_string($errorBody) ? ['raw' => $errorBody] : (is_array($errorBody) ? $errorBody : ['message' => $errorBody]);
            if (is_string($errorBody) && preg_match('/^[\{\[]/', trim($errorBody))) {
                $decoded = json_decode($errorBody, true);
                $errorData = is_array($decoded) ? $decoded : $errorData;
            }

            Log::error('Tochka Payment: Operation status request failed', [
                'url' => $endpoint,
                'operation_id' => $operationId,
                'request_headers' => $this->getHeadersForLog($requestHeaders),
                'status_code' => $statusCode,
                'response_body' => $this->maskSensitiveData($errorData),
            ]);

            // Handle specific HTTP status codes
            switch ($statusCode) {
                case 400:
                    throw new \Exception('Bad Request: Invalid operation ID. ' . $errorBody);
                case 401:
                    throw new \Exception('Unauthorized: Invalid Bearer token. ' . $errorBody);
                case 403:
                    throw new \Exception('Forbidden: Insufficient permissions. ' . $errorBody);
                case 404:
                    throw new \Exception('Not Found: Operation not found. ' . $errorBody);
                case 500:
                    throw new \Exception('Server Error: Tochka API server error. ' . $errorBody);
                default:
                    throw new \Exception('API request failed: ' . $errorBody);
            }
        }
    }

    /**
     * Map API status to payment status constant.
     *
     * @param  string  $apiStatus
     * @return string
     */
    public function mapApiStatusToPaymentStatus(string $apiStatus): string
    {
        $modelClass = \Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy::modelClass();
        
        switch (strtoupper($apiStatus)) {
            case 'APPROVED':
                return $modelClass::STATUS_PAID;

            case 'EXPIRED':
            case 'REFUNDED':
                return $modelClass::STATUS_FAILED;

            case 'ON-REFUND':
            case 'CREATED':
            default:
                return $modelClass::STATUS_PENDING;
        }
    }

    /**
     * Check if API status indicates successful payment.
     *
     * @param  string  $apiStatus
     * @return bool
     */
    public function isSuccessfulStatus(string $apiStatus): bool
    {
        return strtoupper($apiStatus) === 'APPROVED';
    }

    /**
     * Check if API status indicates failed payment.
     *
     * @param  string  $apiStatus
     * @return bool
     */
    public function isFailedStatus(string $apiStatus): bool
    {
        $status = strtoupper($apiStatus);
        return in_array($status, ['EXPIRED', 'REFUNDED']);
    }

    /**
     * Get headers for logging: masked in non-local environments, raw on local for debugging.
     *
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    protected function getHeadersForLog(array $headers): array
    {
        return app()->environment('local')
            ? $headers
            : $this->maskHeadersForLog($headers);
    }

    /**
     * Mask sensitive headers for logging.
     *
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    protected function maskHeadersForLog(array $headers): array
    {
        $masked = $headers;

        if (isset($masked['Authorization'])) {
            $masked['Authorization'] = 'Bearer ****';
        }

        return $masked;
    }

    /**
     * Mask sensitive data for logging.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function maskSensitiveData(array $data): array
    {
        $masked = $data;

        // Mask bearer token if present
        if (isset($masked['Authorization'])) {
            $masked['Authorization'] = 'Bearer ****';
        }

        // Mask consumerId (UUID)
        if (isset($masked['consumerId']) && is_string($masked['consumerId'])) {
            $masked['consumerId'] = substr($masked['consumerId'], 0, 8) . '...';
        }

        // Mask customerCode
        if (isset($masked['customerCode']) && is_string($masked['customerCode'])) {
            $masked['customerCode'] = substr($masked['customerCode'], 0, 4) . '****';
        }

        // Mask merchantId
        if (isset($masked['merchantId']) && is_string($masked['merchantId'])) {
            $masked['merchantId'] = substr($masked['merchantId'], 0, 6) . '****';
        }

        // Recursively mask nested Data array (request/response payload)
        if (isset($masked['Data']) && is_array($masked['Data'])) {
            $masked['Data'] = $this->maskSensitiveData($masked['Data']);
        }

        return $masked;
    }
}
