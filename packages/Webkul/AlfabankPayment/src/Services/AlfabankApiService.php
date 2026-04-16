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
     * AlfaPay base url override (optional).
     */
    protected string $alfaPayBaseUrl = '';

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
        $this->alfaPayBaseUrl = core()->getConfigData('sales.payment_methods.alfabank.alfa_pay_base_url') ?? '';
    }

    /**
     * Get base URL based on test mode.
     */
    protected function getBaseUrl(): string
    {
        $configuredBaseUrl = trim($this->alfaPayBaseUrl);

        // If user provided base url in admin settings, prefer it.
        if ($configuredBaseUrl !== '') {
            return rtrim($configuredBaseUrl, '/') . '/';
        }

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

        return $this->sendRequest($url, $data, [], 'registerOrder');
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

        return $this->sendRequest($url, $data, [], 'getOrderStatus');
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

        return $this->sendRequest($url, $data, [], 'getBindings');
    }

    /**
     * Unbind (remove) a saved card from the bank.
     *
     * @param  string  $bindingId
     * @return array
     */
    public function unbindCard(string $bindingId): array
    {
        $url = $this->getBaseUrl() . 'unbindCard.do';

        $data = [
            'userName'   => $this->merchant,
            'bindingId'  => $bindingId,
        ];

        if ($this->token) {
            $data['token'] = $this->token;
        } else {
            $data['password'] = $this->password;
        }

        return $this->sendRequest($url, $data, [], 'unbindCard');
    }

    /**
     * Refund captured payment amount for a registered bank order.
     *
     * @param  string  $orderId
     * @param  int  $amount  Amount in minimal currency units (kopecks/cents).
     * @param  string|null  $language
     * @param  string|null  $currency  Numeric ISO 4217 code.
     * @return array
     */
    public function refundOrder(
        string $orderId,
        int $amount,
        ?string $language = null,
        ?string $currency = null
    ): array {
        $url = $this->getBaseUrl() . 'refund.do';

        $data = [
            'userName' => $this->merchant,
            'orderId'  => $orderId,
            'amount'   => $amount,
        ];

        if ($this->token) {
            $data['token'] = $this->token;
        } else {
            $data['password'] = $this->password;
        }

        if (! empty($language)) {
            $data['language'] = $language;
        }

        if (! empty($currency)) {
            $data['currency'] = $currency;
        }

        return $this->sendRequest($url, $data, [], 'refundOrder');
    }

    /**
     * Pay with new card using seToken.
     */
    public function payWithNewCard(string $mdOrder, string $seToken): array
    {
        $url = $this->getBaseUrl() . 'paymentorder.do';

        $data = [
            'userName'  => $this->merchant,
            'MDORDER'   => $mdOrder,
            'seToken'   => $seToken,
            'TEXT'      => 'CARDHOLDER',
            'language'  => 'ru',
            'threeDSSDK' => 'false',
        ];

        if ($this->token) {
            $data['token'] = $this->token;
        } else {
            $data['password'] = $this->password;
        }

        return $this->sendRequest($url, $data, [], 'payWithNewCard');
    }

    /**
     * Pay with saved card (binding).
     */
    public function payWithBinding(string $mdOrder, string $bindingId, ?string $cvc = null): array
    {
        $url = $this->getBaseUrl() . 'paymentOrderBinding.do';

        $data = [
            'userName'  => $this->merchant,
            'mdOrder'   => $mdOrder,
            'bindingId' => $bindingId,
            'language'  => 'ru',
        ];

        if ($this->token) {
            $data['token'] = $this->token;
        } else {
            $data['password'] = $this->password;
        }

        if ($cvc !== null && $cvc !== '') {
            $data['cvc'] = $cvc;
        }

        return $this->sendRequest($url, $data, [], 'payWithBinding');
    }

    /**
     * Reverse (cancel) a payment order.
     */
    public function reverseOrder(string $orderId): array
    {
        $url = $this->getBaseUrl() . 'reverse.do';

        $data = [
            'userName' => $this->merchant,
            'orderId'  => $orderId,
            'language' => 'ru',
        ];

        if ($this->token) {
            $data['token'] = $this->token;
        } else {
            $data['password'] = $this->password;
        }

        return $this->sendRequest($url, $data, [], 'reverseOrder');
    }

    /**
     * Get public key for seToken encryption (se/keys.do).
     */
    public function getPublicKey(): array
    {
        $baseUrl = $this->getBaseUrl();
        // se/keys.do is at the payment root, not under /rest/
        $url = preg_replace('#/rest/$#', '/', $baseUrl) . 'se/keys.do';

        $data = [];

        return $this->sendRequest($url, $data, [], 'getPublicKey');
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
     * Max length of raw response body to store in log (bytes).
     */
    protected const LOG_RAW_RESPONSE_MAX_LENGTH = 2048;

    /**
     * Send HTTP request to Alfa-Bank API.
     *
     * @param  string  $url
     * @param  array  $data
     * @param  array  $headers
     * @param  string  $method  Method name for logging (e.g. registerOrder, getOrderStatus).
     * @return array
     */
    protected function sendRequest(string $url, array $data, array $headers = [], string $method = 'request'): array
    {
        $sanitizedRequest = $this->sanitizeLogData($data);
        $startTime = microtime(true);

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
            CURLOPT_SSL_VERIFYPEER => ! $this->testMode,
            CURLOPT_SSL_VERIFYHOST => $this->testMode ? 0 : 2,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        if ($curlError !== '') {
            $this->logExchange($method, $url, $sanitizedRequest, null, $durationMs, null, 'cURL error: ' . $curlError);
            Log::error('Alfabank API cURL error: ' . $curlError);
            throw new \Exception('Payment gateway connection error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $rawTruncated = is_string($response) ? $this->truncateForLog($response) : (string) $response;
            $this->logExchange($method, $url, $sanitizedRequest, $httpCode, $durationMs, $rawTruncated, 'HTTP ' . $httpCode);
            Log::error('Alfabank API HTTP error: ' . $httpCode);
            throw new \Exception('Payment gateway returned HTTP code: ' . $httpCode);
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $rawTruncated = is_string($response) ? $this->truncateForLog($response) : (string) $response;
            $this->logExchange($method, $url, $sanitizedRequest, $httpCode, $durationMs, $rawTruncated, 'JSON decode: ' . json_last_error_msg());
            Log::error('Alfabank API JSON decode error: ' . json_last_error_msg());
            throw new \Exception('Invalid response from payment gateway');
        }

        $this->logExchange($method, $url, $sanitizedRequest, $httpCode, $durationMs, $decoded, null);

        if (config('alfabank-payment.log_enabled', true)) {
            $channel = config('alfabank-payment.log_channel', 'daily');
            Log::channel($channel)->info('Alfabank bank response', [
                'method'   => $method,
                'response' => $decoded,
            ]);
        }

        return $decoded;
    }

    /**
     * Log API request/response exchange (success or failure).
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array  $requestData  Sanitized request data.
     * @param  int|null  $httpCode
     * @param  float  $durationMs
     * @param  array|string|null  $response  Decoded array or raw string (truncated).
     * @param  string|null  $error  Short error description when applicable.
     * @return void
     */
    protected function logExchange(
        string $method,
        string $url,
        array $requestData,
        ?int $httpCode,
        float $durationMs,
        array|string|null $response,
        ?string $error = null
    ): void {
        if (! config('alfabank-payment.log_enabled', true)) {
            return;
        }

        $channel = config('alfabank-payment.log_channel', 'daily');

        $logData = [
            'method'     => $method,
            'url'        => $url,
            'request'    => $requestData,
            'http_code'  => $httpCode,
            'duration_ms' => $durationMs,
            'response'   => $response,
            'error'      => $error,
        ];

        Log::channel($channel)->info('Alfabank API Request', $logData);
    }

    /**
     * Truncate string for safe logging.
     *
     * @param  string  $value
     * @return string
     */
    protected function truncateForLog(string $value): string
    {
        if (strlen($value) <= self::LOG_RAW_RESPONSE_MAX_LENGTH) {
            return $value;
        }

        return substr($value, 0, self::LOG_RAW_RESPONSE_MAX_LENGTH) . '... [truncated]';
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
