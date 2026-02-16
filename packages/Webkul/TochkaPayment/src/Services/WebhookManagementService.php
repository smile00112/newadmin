<?php

namespace Webkul\TochkaPayment\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class WebhookManagementService
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
     * Webhook type constant.
     */
    const WEBHOOK_TYPE_ACQUIRING_INTERNET_PAYMENT = 'acquiringInternetPayment';

    /**
     * Create a new webhook management service instance.
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
     * Subscribe to webhook.
     *
     * @param  int  $companyId
     * @param  string|null  $webhookUrl
     * @param  string  $webhookType
     * @return array
     * @throws \Exception
     */
    public function subscribeToWebhook(int $companyId, ?string $webhookUrl = null, string $webhookType = self::WEBHOOK_TYPE_ACQUIRING_INTERNET_PAYMENT): array
    {
        $settings = $this->settingsService->getSettings($companyId);

        // Validate required settings
        if (empty($settings['client_id'])) {
            throw new \Exception('Client ID is required to subscribe to webhook');
        }

        if (empty($settings['jwt_token'])) {
            throw new \Exception('JWT token is required to subscribe to webhook');
        }

        if (empty($settings['api_base_url'])) {
            throw new \Exception('API base URL is required to subscribe to webhook');
        }

        // Determine webhook URL
        if (empty($webhookUrl)) {
            $webhookUrl = $settings['webhook_url'];
        }

        if (empty($webhookUrl)) {
            // Generate webhook URL automatically
            $webhookUrl = url(route('api.tochka-payment.webhook.handle', [], false));
        }

        // Validate HTTPS requirement
        if (!str_starts_with($webhookUrl, 'https://')) {
            throw new \Exception('Webhook URL must use HTTPS protocol');
        }

        $apiBaseUrl = rtrim($settings['api_base_url'], '/');
        $endpoint = $apiBaseUrl . '/webhook/v1.0/' . $settings['client_id'];
        $bearerToken = $settings['jwt_token'];

        $requestPayload = [
            'Data' => [
                'webhookType' => $webhookType,
                'url' => $webhookUrl,
            ],
        ];

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
        ];

        Log::info('Tochka Payment: Subscribe to webhook', [
            'url' => $endpoint,
            'webhook_type' => $webhookType,
            'webhook_url' => $webhookUrl,
            'company_id' => $companyId,
            'headers' => $this->getHeadersForLog($requestHeaders),
        ]);

        try {
            $response = $this->client->put($endpoint, [
                'json' => $requestPayload,
                'headers' => $requestHeaders,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Tochka API: ' . json_last_error_msg());
            }

            Log::info('Tochka Payment: Webhook subscription successful', [
                'url' => $endpoint,
                'status_code' => $statusCode,
                'response_body' => $this->maskSensitiveData(is_array($responseData) ? $responseData : ['raw' => $responseBody]),
                'company_id' => $companyId,
            ]);

            return [
                'success' => true,
                'message' => 'Webhook subscription created successfully',
                'data' => $responseData,
            ];

        } catch (GuzzleException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            $errorData = is_string($errorBody) ? ['raw' => $errorBody] : (is_array($errorBody) ? $errorBody : ['message' => $errorBody]);
            if (is_string($errorBody) && preg_match('/^[\{\[]/', trim($errorBody))) {
                $decoded = json_decode($errorBody, true);
                $errorData = is_array($decoded) ? $decoded : $errorData;
            }

            Log::error('Tochka Payment: Webhook subscription failed', [
                'url' => $endpoint,
                'company_id' => $companyId,
                'request_headers' => $this->getHeadersForLog($requestHeaders),
                'request_body' => $this->maskSensitiveData($requestPayload),
                'status_code' => $statusCode,
                'response_body' => $this->maskSensitiveData($errorData),
            ]);

            // Handle specific HTTP status codes
            switch ($statusCode) {
                case 400:
                    throw new \Exception('Bad Request: Invalid webhook parameters. ' . $errorBody);
                case 401:
                    throw new \Exception('Unauthorized: Invalid Bearer token. ' . $errorBody);
                case 403:
                    throw new \Exception('Forbidden: Insufficient permissions. ' . $errorBody);
                case 404:
                    throw new \Exception('Not Found: Client ID not found. ' . $errorBody);
                case 500:
                    throw new \Exception('Server Error: Tochka API server error. ' . $errorBody);
                default:
                    throw new \Exception('Webhook subscription failed: ' . $errorBody);
            }
        }
    }

    /**
     * Unsubscribe from webhook.
     *
     * @param  int  $companyId
     * @param  string  $webhookType
     * @return array
     * @throws \Exception
     */
    public function unsubscribeFromWebhook(int $companyId, string $webhookType = self::WEBHOOK_TYPE_ACQUIRING_INTERNET_PAYMENT): array
    {
        $settings = $this->settingsService->getSettings($companyId);

        // Validate required settings
        if (empty($settings['client_id'])) {
            throw new \Exception('Client ID is required to unsubscribe from webhook');
        }

        if (empty($settings['jwt_token'])) {
            throw new \Exception('JWT token is required to unsubscribe from webhook');
        }

        if (empty($settings['api_base_url'])) {
            throw new \Exception('API base URL is required to unsubscribe from webhook');
        }

        $apiBaseUrl = rtrim($settings['api_base_url'], '/');
        $endpoint = $apiBaseUrl . '/webhook/v1.0/' . $settings['client_id'] . '?webhookType=' . urlencode($webhookType);
        $bearerToken = $settings['jwt_token'];

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
        ];

        Log::info('Tochka Payment: Unsubscribe from webhook', [
            'url' => $endpoint,
            'webhook_type' => $webhookType,
            'company_id' => $companyId,
            'headers' => $this->getHeadersForLog($requestHeaders),
        ]);

        try {
            $response = $this->client->delete($endpoint, [
                'headers' => $requestHeaders,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Empty response is OK for DELETE
                $responseData = [];
            }

            Log::info('Tochka Payment: Webhook unsubscription successful', [
                'url' => $endpoint,
                'status_code' => $statusCode,
                'response_body' => $this->maskSensitiveData(is_array($responseData) ? $responseData : ['raw' => $responseBody]),
                'company_id' => $companyId,
            ]);

            return [
                'success' => true,
                'message' => 'Webhook unsubscription successful',
                'data' => $responseData,
            ];

        } catch (GuzzleException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            $errorData = is_string($errorBody) ? ['raw' => $errorBody] : (is_array($errorBody) ? $errorBody : ['message' => $errorBody]);
            if (is_string($errorBody) && preg_match('/^[\{\[]/', trim($errorBody))) {
                $decoded = json_decode($errorBody, true);
                $errorData = is_array($decoded) ? $decoded : $errorData;
            }

            Log::error('Tochka Payment: Webhook unsubscription failed', [
                'url' => $endpoint,
                'company_id' => $companyId,
                'request_headers' => $this->getHeadersForLog($requestHeaders),
                'status_code' => $statusCode,
                'response_body' => $this->maskSensitiveData($errorData),
            ]);

            // Handle specific HTTP status codes
            switch ($statusCode) {
                case 400:
                    throw new \Exception('Bad Request: Invalid webhook parameters. ' . $errorBody);
                case 401:
                    throw new \Exception('Unauthorized: Invalid Bearer token. ' . $errorBody);
                case 403:
                    throw new \Exception('Forbidden: Insufficient permissions. ' . $errorBody);
                case 404:
                    throw new \Exception('Not Found: Webhook not found. ' . $errorBody);
                case 500:
                    throw new \Exception('Server Error: Tochka API server error. ' . $errorBody);
                default:
                    throw new \Exception('Webhook unsubscription failed: ' . $errorBody);
            }
        }
    }

    /**
     * Get list of webhooks.
     *
     * @param  int  $companyId
     * @return array
     * @throws \Exception
     */
    public function getWebhooks(int $companyId): array
    {
        $settings = $this->settingsService->getSettings($companyId);

        // Validate required settings
        if (empty($settings['client_id'])) {
            throw new \Exception('Client ID is required to get webhooks');
        }

        if (empty($settings['jwt_token'])) {
            throw new \Exception('JWT token is required to get webhooks');
        }

        if (empty($settings['api_base_url'])) {
            throw new \Exception('API base URL is required to get webhooks');
        }

        $apiBaseUrl = rtrim($settings['api_base_url'], '/');
        $endpoint = $apiBaseUrl . '/webhook/v1.0/' . $settings['client_id'];
        $bearerToken = $settings['jwt_token'];

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
        ];

        Log::info('Tochka Payment: Get webhooks', [
            'url' => $endpoint,
            'company_id' => $companyId,
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

            Log::info('Tochka Payment: Get webhooks successful', [
                'url' => $endpoint,
                'status_code' => $statusCode,
                'response_body' => $this->maskSensitiveData(is_array($responseData) ? $responseData : ['raw' => $responseBody]),
                'company_id' => $companyId,
            ]);

            return [
                'success' => true,
                'data' => $responseData,
            ];

        } catch (GuzzleException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            $errorData = is_string($errorBody) ? ['raw' => $errorBody] : (is_array($errorBody) ? $errorBody : ['message' => $errorBody]);
            if (is_string($errorBody) && preg_match('/^[\{\[]/', trim($errorBody))) {
                $decoded = json_decode($errorBody, true);
                $errorData = is_array($decoded) ? $decoded : $errorData;
            }

            Log::error('Tochka Payment: Get webhooks failed', [
                'url' => $endpoint,
                'company_id' => $companyId,
                'request_headers' => $this->getHeadersForLog($requestHeaders),
                'status_code' => $statusCode,
                'response_body' => $this->maskSensitiveData($errorData),
            ]);

            // Handle specific HTTP status codes
            switch ($statusCode) {
                case 400:
                    throw new \Exception('Bad Request: Invalid request parameters. ' . $errorBody);
                case 401:
                    throw new \Exception('Unauthorized: Invalid Bearer token. ' . $errorBody);
                case 403:
                    throw new \Exception('Forbidden: Insufficient permissions. ' . $errorBody);
                case 404:
                    throw new \Exception('Not Found: Client ID not found. ' . $errorBody);
                case 500:
                    throw new \Exception('Server Error: Tochka API server error. ' . $errorBody);
                default:
                    throw new \Exception('Get webhooks failed: ' . $errorBody);
            }
        }
    }

    /**
     * Check if subscribed to webhook.
     *
     * @param  int  $companyId
     * @param  string  $webhookType
     * @return bool
     */
    public function isSubscribed(int $companyId, string $webhookType = self::WEBHOOK_TYPE_ACQUIRING_INTERNET_PAYMENT): bool
    {
        try {
            $webhooks = $this->getWebhooks($companyId);
            
            // Check if webhooks list contains the requested type
            if (isset($webhooks['data']['Data']) && is_array($webhooks['data']['Data'])) {
                foreach ($webhooks['data']['Data'] as $webhook) {
                    if (isset($webhook['webhookType']) && $webhook['webhookType'] === $webhookType) {
                        return true;
                    }
                }
            }

            // Alternative structure check
            if (isset($webhooks['data']) && is_array($webhooks['data'])) {
                foreach ($webhooks['data'] as $webhook) {
                    if (isset($webhook['webhookType']) && $webhook['webhookType'] === $webhookType) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::warning('Tochka Payment: Failed to check webhook subscription status', [
                'company_id' => $companyId,
                'webhook_type' => $webhookType,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
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
    protected function maskSensitiveData(array $data): array
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
