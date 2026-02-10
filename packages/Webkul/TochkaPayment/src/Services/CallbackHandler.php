<?php

namespace Webkul\TochkaPayment\Services;

use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Exceptions\InvalidRequestException;
use Webkul\TochkaPayment\Exceptions\InvalidSignatureException;

class CallbackHandler
{
    /**
     * Required callback parameters.
     *
     * @var array
     */
    protected const REQUIRED_PARAMS = [
        'id',
        'sum',
        'clientid',
        'orderid',
        'key',
        'login',
    ];

    /**
     * Process and validate callback request.
     *
     * @param  array  $postData
     * @return array
     * @throws InvalidRequestException
     * @throws InvalidSignatureException
     */
    public function process(array $postData): array
    {
        Log::info('Tochka Payment: Bank callback received (raw)', $this->maskArray($postData));

        // Validate HTTP method
        if (request()->method() !== 'POST') {
            throw new InvalidRequestException('Invalid HTTP method. Expected POST.');
        }

        // Check required parameters
        foreach (self::REQUIRED_PARAMS as $param) {
            if (empty($postData[$param])) {
                throw new InvalidRequestException("Missing required parameter: {$param}");
            }
        }

        // Validate signature
        if (!$this->isSignatureValid($postData)) {
            throw new InvalidSignatureException('Invalid callback signature');
        }

        // Extract order ID
        $rawOrderId = $postData['orderid'];
        $parts = explode('|', $rawOrderId);
        $paymentId = isset($parts[0]) ? (int) $parts[0] : 0;

        if ($paymentId <= 0) {
            throw new InvalidRequestException("Invalid orderid format: {$rawOrderId}");
        }

        return [
            'payment_id' => $paymentId,
            'order_id' => $rawOrderId,
            'transaction_id' => $postData['id'],
            'amount' => (float) $postData['sum'],
            'client_name' => $postData['clientid'],
            'callback_data' => $postData,
        ];
    }

    /**
     * Validate callback signature.
     *
     * @param  array  $postData
     * @return bool
     */
    public function isSignatureValid(array $postData): bool
    {
        $secretKey = (string) (config('tochka-payment.secret_key') ?? '');
        $stringToHash = $postData['id'] .
            $postData['sum'] .
            $postData['clientid'] .
            $postData['orderid'] .
            $postData['login'] .
            $secretKey;

        $expectedKey = hash(config('tochka-payment.signature_algorithms.callback'), $stringToHash);

        return hash_equals($expectedKey, $postData['key']);
    }

    /**
     * Generate success response for bank.
     *
     * @param  string  $transactionId
     * @return string
     */
    public function getSuccessResponse(string $transactionId): string
    {
        $secretKey = (string) (config('tochka-payment.secret_key') ?? '');
        $hash = hash(
            config('tochka-payment.signature_algorithms.callback'),
            $transactionId . $secretKey
        );

        return 'OK ' . $hash;
    }

    /**
     * Mask sensitive data for logging.
     *
     * @param  array  $array
     * @return array
     */
    protected function maskArray(array $array): array
    {
        $keysToMask = ['login', 'key'];

        foreach ($keysToMask as $key) {
            if (isset($array[$key])) {
                $array[$key] = '****';
            }
        }

        return $array;
    }
}
