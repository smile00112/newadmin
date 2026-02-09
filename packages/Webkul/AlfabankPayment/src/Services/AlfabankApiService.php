<?php

namespace Webkul\AlfabankPayment\Services;

use Illuminate\Support\Facades\Log;

class AlfabankApiService
{
    /**
     * Test URL for Alfa-Bank API.
     */
    protected string $testUrl = 'https://alfa.rbsuat.com/payment/rest/';

    /**
     * Production URL for Alfa-Bank API.
     */
    protected string $prodUrl = 'https://pay.alfabank.ru/payment/rest/';

    /**
     * Merchant login.
     */
    protected string $merchant;

    /**
     * Merchant password.
     */
    protected string $password;

    /**
     * Token for authentication (alternative to login/password).
     */
    protected ?string $token = null;

    /**
     * Test mode flag.
     */
    protected bool $testMode;

    /**
     * Stage mode: 'one-stage' or 'two-stage'.
     */
    protected string $stageMode;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->merchant = core()->getConfigData('sales.payment_methods.alfabank.merchant') ?? '';
        $this->password = core()->getConfigData('sales.payment_methods.alfabank.password') ?? '';
        $this->token = core()->getConfigData('sales.payment_methods.alfabank.token');
        $this->testMode = (bool) core()->getConfigData('sales.payment_methods.alfabank.test_mode');
        $this->stageMode = core()->getConfigData('sales.payment_methods.alfabank.stage_mode') ?? 'one-stage';
    }

    /**
     * Get base URL based on test mode.
     */
    protected function getBaseUrl(): string
    {
        return $this->testMode ? $this->testUrl : $this->prodUrl;
    }

    /**
     * Register order for payment.
     *
     * @param  array  $orderData
     * @return array
     */
    public function registerOrder(array $orderData): array
    {
        $endpoint = $this->stageMode === 'two-stage' ? 'registerPreAuth.do' : 'register.do';
        $url = $this->getBaseUrl() . $endpoint;

        $data = $this->buildRegistrationData($orderData);

        $response = $this->sendRequest($url, $data);

        $this->logRequest('registerOrder', $url, $data, $response);

        return $response;
    }

    /**
     * Get order status.
     *
     * @param  string  $orderId
     * @return array
     */
    public function getOrderStatus(string $orderId): array
    {
        $url = $this->getBaseUrl() . 'getOrderStatusExtended.do';

        $data = [
            'userName' => $this->merchant,
            'orderId'  => $orderId,
        ];

        if ($this->token) {
            $data['token'] = $this->token;
        } else {
            $data['password'] = $this->password;
        }

        $response = $this->sendRequest($url, $data);

        $this->logRequest('getOrderStatus', $url, $data, $response);

        return $response;
    }

    /**
     * Get bindings (saved cards) for a client.
     *
     * @param  string  $clientId
     * @return array
     */
    public function getBindings(string $clientId): array
    {
        $url = $this->getBaseUrl() . 'getBindings.do';

        $data = [
            'userName'    => $this->merchant,
            'clientId'    => $clientId,
            'bindingType' => 'C',
        ];

        if ($this->token) {
            $data['token'] = $this->token;
        } else {
            $data['password'] = $this->password;
        }

        $response = $this->sendRequest($url, $data);

        $this->logRequest('getBindings', $url, $data, $response);

        return $response;
    }

    /**
     * Build registration data for order.
     *
     * @param  array  $orderData
     * @return array
     */
    protected function buildRegistrationData(array $orderData): array
    {
        $data = [
            'userName'    => $this->merchant,
            'orderNumber' => $orderData['orderNumber'],
            'amount'      => $orderData['amount'],
            'returnUrl'   => $orderData['returnUrl'],
        ];

        if ($this->token) {
            $data['token'] = $this->token;
        } else {
            $data['password'] = $this->password;
        }

        if (!empty($orderData['failUrl'])) {
            $data['failUrl'] = $orderData['failUrl'];
        }

        if (!empty($orderData['clientId'])) {
            $data['clientId'] = $orderData['clientId'];
        }

        if (!empty($orderData['bindingId'])) {
            $data['bindingId'] = $orderData['bindingId'];
        }

        if (!empty($orderData['orderBundle'])) {
            $data['orderBundle'] = is_string($orderData['orderBundle'])
                ? $orderData['orderBundle']
                : json_encode($orderData['orderBundle']);
        }

        if (!empty($orderData['jsonParams'])) {
            $data['jsonParams'] = is_string($orderData['jsonParams'])
                ? $orderData['jsonParams']
                : json_encode($orderData['jsonParams']);
        }

        if (!empty($orderData['currency'])) {
            $data['currency'] = $orderData['currency'];
        }

        if (!empty($orderData['description'])) {
            $data['description'] = $orderData['description'];
        }

        if (!empty($orderData['email'])) {
            $data['email'] = $orderData['email'];
        }

        if (!empty($orderData['dynamicCallbackUrl'])) {
            $data['dynamicCallbackUrl'] = $orderData['dynamicCallbackUrl'];
        }

        return $data;
    }

    /**
     * Send HTTP request to Alfa-Bank API.
     *
     * @param  string  $url
     * @param  array  $data
     * @param  array  $headers
     * @return array
     */
    protected function sendRequest(string $url, array $data, array $headers = []): array
    {
        $ch = curl_init();

        $defaultHeaders = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS    => http_build_query($data, '', '&'),
            CURLOPT_HTTPHEADER     => array_merge($defaultHeaders, $headers),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            Log::error('Alfabank API cURL error: ' . $error);
            throw new \Exception('Payment gateway connection error: ' . $error);
        }

        if ($httpCode !== 200) {
            Log::error('Alfabank API HTTP error: ' . $httpCode);
            throw new \Exception('Payment gateway returned HTTP code: ' . $httpCode);
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Alfabank API JSON decode error: ' . json_last_error_msg());
            throw new \Exception('Invalid response from payment gateway');
        }

        return $decoded;
    }

    /**
     * Log API request/response.
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array  $requestData
     * @param  array  $response
     * @return void
     */
    protected function logRequest(string $method, string $url, array $requestData, array $response): void
    {
        $logData = [
            'method' => $method,
            'url'    => $url,
            'request' => $this->sanitizeLogData($requestData),
            'response' => $response,
        ];

        Log::channel('daily')->info('Alfabank API Request', $logData);
    }

    /**
     * Sanitize sensitive data for logging.
     *
     * @param  array  $data
     * @return array
     */
    protected function sanitizeLogData(array $data): array
    {
        $sanitized = $data;

        if (isset($sanitized['password'])) {
            $sanitized['password'] = '***';
        }

        if (isset($sanitized['token'])) {
            $sanitized['token'] = '***';
        }

        return $sanitized;
    }
}
