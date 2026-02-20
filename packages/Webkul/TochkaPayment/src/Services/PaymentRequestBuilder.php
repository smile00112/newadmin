<?php

namespace Webkul\TochkaPayment\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;

class PaymentRequestBuilder
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
     * Buyer service instance.
     *
     * @var \Webkul\TochkaPayment\Services\TochkaPaymentBuyerService
     */
    protected $buyerService;

    /**
     * Create a new payment request builder instance.
     */
    public function __construct(?SettingsService $settingsService = null, ?TochkaPaymentBuyerService $buyerService = null)
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
        $this->settingsService = $settingsService ?? new SettingsService();
        $this->buyerService = $buyerService ?? new TochkaPaymentBuyerService();
    }

    /**
     * Build payment request parameters for Tochka Bank API.
     *
     * @param  array  $data
     * @param  int  $paymentId
     * @param  int|null  $companyId
     * @return array
     */
    public function buildRequestParams(array $data, int $paymentId, ?int $companyId = null): array
    {
        // Get settings for company
        $settings = $this->settingsService->getSettings($companyId);

        $orderId = $paymentId . '|' . time();
        $amount = number_format((float) $data['amount'], 2, '.', '');

        // Build purpose from product name or client name
        $purpose = $data['product_name'] ?? 'Оплата заказа';
        if (!empty($data['client_name'])) {
            $purpose .= ' (' . trim($data['client_name']) . ')';
        }

        // Get callback URLs (include payment_id so redirect controller can resolve payment)
        $callbackUrl = $this->getCallbackUrl();
        $baseUrl = rtrim(config('app.url'), '/');
        $successPath = $data['success_redirect_path'] ?? '/payment/tochka/success';
        $failPath = $data['fail_redirect_path'] ?? '/payment/tochka/fail';
        $successUrl = $baseUrl . $successPath . '?payment_id=' . $paymentId;
        $failUrl = $baseUrl . $failPath . '?payment_id=' . $paymentId;

        // Build Data object according to API documentation.
        // Merchant ID and Customer Code from settings are included in the order creation request.
        $customerCode = $data['customer_code'] ?? $settings['customer_code'] ?? '';
        $merchantId = $settings['merchant_id'] ?? '';

        // consumerId only from buyer (per-payer), never from settings
        $consumerId = $this->buyerService->getConsumerIdForPayment(
            $companyId,
            $data['client_email'] ?? null
        );

        $dataPayload = [
            'customerCode' => $customerCode,
            'merchantId' => $merchantId,
            'amount' => $amount,
            'purpose' => $purpose,
            'redirectUrl' => $successUrl,
            'failRedirectUrl' => $failUrl,
            'paymentMode' => $settings['payment_mode'],
            'saveCard' => (bool) $settings['save_card'],
            'preAuthorization' => (bool) $settings['pre_authorization'],
            'ttl' => (int) $settings['ttl'],
        ];

        if (! empty($consumerId)) {
            $dataPayload['consumerId'] = $consumerId;
        }

        $requestData = ['Data' => $dataPayload];

        // Add paymentLinkId if provided
        if (!empty($data['payment_link_id'])) {
            $requestData['Data']['paymentLinkId'] = $data['payment_link_id'];
        }

        // Store orderId, paymentId, and companyId for reference
        $requestData['_orderId'] = $orderId;
        $requestData['_paymentId'] = $paymentId;
        $requestData['_companyId'] = $companyId;

        return $requestData;
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
     * Request payment URL from Tochka Bank API.
     *
     * @param  array  $requestData
     * @param  int|null  $companyId
     * @return array Returns array with 'paymentUrl', 'paymentLinkId', 'consumerId', and full response
     * @throws \Exception
     */
    public function requestPaymentUrl(array $requestData, ?int $companyId = null): array
    {
        // Get company ID from request data if not provided
        if ($companyId === null) {
            $companyId = $requestData['_companyId'] ?? null;
        }

        // Get settings for company
        $settings = $this->settingsService->getSettings($companyId);

        Log::info('GET requestPaymentUrl', [
            '$settings' => $settings,
            '$requestData' => $requestData,
            '-$companyId'  =>  $companyId,
        ]);

        $apiBaseUrl = $settings['api_base_url'];
        $bearerToken = $settings['jwt_token'];

        if (empty($apiBaseUrl)) {
            throw new \Exception('Tochka API base URL is not configured');
        }

        if (empty($bearerToken)) {
            throw new \Exception('Tochka JWT token is not configured');
        }

        $endpoint = rtrim($apiBaseUrl, '/') . '/acquiring/v1.0/payments';

        // Extract Data object and metadata
        $orderId = $requestData['_orderId'] ?? null;
        $paymentId = $requestData['_paymentId'] ?? null;

        // Extract Data payload - it should already be wrapped in 'Data' key
        $dataPayload = $requestData['Data'] ?? $requestData;

        // Remove metadata from payload if present
        if (isset($dataPayload['_orderId'])) {
            unset($dataPayload['_orderId']);
        }
        if (isset($dataPayload['_paymentId'])) {
            unset($dataPayload['_paymentId']);
        }

        // Ensure Data is wrapped correctly
        $requestPayload = isset($requestData['Data'])
            ? ['Data' => $dataPayload]
            : ['Data' => $dataPayload];

        $requestHeaders = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
        ];

        Log::info('Tochka Payment: Request to bank', [
            'url' => $endpoint,
            'headers' => $this->getHeadersForLog($requestHeaders),
            'body' => $this->maskSensitiveData($requestPayload),
            'payment_id' => $paymentId,
            'order_id' => $orderId,
        ]);

        try {
            $response = $this->client->post($endpoint, [
                'json' => $requestPayload,
                'headers' => $requestHeaders,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Tochka API: ' . json_last_error_msg());
            }

            Log::info('Tochka Payment: Response from bank', [
                'url' => $endpoint,
                'status_code' => $statusCode,
                'response_body' => $this->maskSensitiveData(is_array($responseData) ? $responseData : ['raw' => $responseBody]),
                'payment_id' => $paymentId,
                'order_id' => $orderId,
            ]);

            // Extract payment URL from response
            // Response structure may vary, check common fields
            $paymentUrl = $responseData['paymentUrl']
                ?? $responseData['Data']['paymentUrl']
                ?? $responseData['Data']['paymentLink']
                ?? $responseData['url']
                ?? $responseData['link']
                ?? null;

            if (empty($paymentUrl)) {
                Log::warning('Tochka Payment: Payment URL not found in response', [
                    'response' => $responseData,
                    'payment_id' => $paymentId,
                ]);
                throw new \Exception('Payment URL not found in API response');
            }

            $result = [
                'paymentUrl' => $paymentUrl,
                'response_data' => $responseData,
            ];

            // Extract paymentLinkId if available
            if (isset($responseData['paymentLinkId'])) {
                $result['paymentLinkId'] = $responseData['paymentLinkId'];
            } elseif (isset($responseData['Data']['paymentLinkId'])) {
                $result['paymentLinkId'] = $responseData['Data']['paymentLinkId'];
            }

            // Extract consumerId from response if available (saved to buyer by adapter/webhook)
            if (isset($responseData['consumerId'])) {
                $result['consumerId'] = $responseData['consumerId'];
            } elseif (isset($responseData['Data']['consumerId'])) {
                $result['consumerId'] = $responseData['Data']['consumerId'];
            }

            return $result;

        } catch (GuzzleException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            $errorData = is_string($errorBody) ? ['raw' => $errorBody] : (is_array($errorBody) ? $errorBody : ['message' => $errorBody]);
            if (is_string($errorBody) && preg_match('/^[\{\[]/', trim($errorBody))) {
                $decoded = json_decode($errorBody, true);
                $errorData = is_array($decoded) ? $decoded : $errorData;
            }

            Log::error('Tochka Payment: API request failed', [
                'url' => $endpoint,
                'request_headers' => $this->getHeadersForLog($requestHeaders),
                'request_body' => $this->maskSensitiveData($requestPayload),
                'status_code' => $statusCode,
                'response_body' => $this->maskSensitiveData($errorData),
                'payment_id' => $paymentId,
                'order_id' => $orderId,
            ]);

            // Handle specific HTTP status codes
            switch ($statusCode) {
                case 400:
                    throw new \Exception('Bad Request: Invalid payment parameters. ' . $errorBody);
                case 401:
                    throw new \Exception('Unauthorized: Invalid Bearer token. ' . $errorBody);
                case 403:
                    throw new \Exception('Forbidden: Insufficient permissions. ' . $errorBody);
                case 404:
                    throw new \Exception('Not Found: API endpoint not found. ' . $errorBody);
                case 500:
                    throw new \Exception('Server Error: Tochka API server error. ' . $errorBody);
                default:
                    throw new \Exception('API request failed: ' . $errorBody);
            }
        }
    }

    /**
     * Build payment URL with form data (legacy method, kept for compatibility).
     *
     * @param  array  $formData
     * @return string
     * @deprecated Use requestPaymentUrl() instead
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
     * @param  int|null  $companyId
     * @param  array|null  $responseData
     * @return \Webkul\TochkaPayment\Models\TochkaPaymentHistory
     */
    public function createPaymentHistory(array $data, array $requestParams, string $paymentUrl, ?int $companyId = null, ?array $responseData = null)
    {
        // Extract order_id from request params if available
        $orderId = $requestParams['_orderId']
            ?? $requestParams['orderid']
            ?? '';

        // Get company ID from request params if not provided
        if ($companyId === null) {
            $companyId = $requestParams['_companyId'] ?? null;
        }

        // If still null, try to get from authenticated admin
        if ($companyId === null) {
            $admin = auth()->guard('admin')->user();
            $companyId = $admin?->company_id;
        }

        $paymentData = [
            'company_id' => $companyId,
            'external_order_id' => $data['external_order_id'] ?? null,
            'order_id' => $orderId,
            'amount' => $data['amount'],
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'],
            'client_phone' => $data['client_phone'],
            'payment_url' => $paymentUrl,
            'status' => 'pending',
            'request_data' => $requestParams ?: null,
        ];

        // Add response data if provided
        if ($responseData) {
            $paymentData['response_data'] = $responseData;

            // Extract consumerId from response if available
            if (isset($responseData['consumerId'])) {
                $paymentData['consumer_id'] = $responseData['consumerId'];
            } elseif (isset($responseData['Data']['consumerId'])) {
                $paymentData['consumer_id'] = $responseData['Data']['consumerId'];
            }

            // Extract operationId if available
            if (isset($responseData['operationId'])) {
                $paymentData['operation_id'] = $responseData['operationId'];
            } elseif (isset($responseData['Data']['operationId'])) {
                $paymentData['operation_id'] = $responseData['Data']['operationId'];
            }

            // Extract paymentLinkId if available
            if (isset($responseData['paymentLinkId'])) {
                $paymentData['payment_link_id'] = $responseData['paymentLinkId'];
            } elseif (isset($responseData['Data']['paymentLinkId'])) {
                $paymentData['payment_link_id'] = $responseData['Data']['paymentLinkId'];
            }
        }

        return TochkaPaymentHistoryProxy::create($paymentData);
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

        // Legacy fields
        if (isset($masked['login'])) {
            $masked['login'] = '****';
        }

        if (isset($masked['sign']) && is_string($masked['sign'])) {
            $masked['sign'] = substr($masked['sign'], 0, 8) . '...';
        }

        // Recursively mask nested Data array (request/response payload)
        if (isset($masked['Data']) && is_array($masked['Data'])) {
            $masked['Data'] = $this->maskSensitiveData($masked['Data']);
        }

        return $masked;
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
}
