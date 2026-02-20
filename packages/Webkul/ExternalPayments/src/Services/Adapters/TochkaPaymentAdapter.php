<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Services\Adapters;

use Illuminate\Support\Facades\Log;
use Webkul\ExternalPayments\Contracts\PaymentProviderAdapterInterface;
use Webkul\TochkaPayment\Services\PaymentRequestBuilder;
use Webkul\TochkaPayment\Services\SettingsService;
use Webkul\TochkaPayment\Services\TochkaPaymentBuyerService;

class TochkaPaymentAdapter implements PaymentProviderAdapterInterface
{
    public function __construct(
        protected PaymentRequestBuilder $requestBuilder,
        protected ?SettingsService $settingsService = null,
        protected ?TochkaPaymentBuyerService $buyerService = null
    ) {
        $this->settingsService = $this->settingsService ?? new SettingsService();
        $this->buyerService = $this->buyerService ?? new TochkaPaymentBuyerService();
    }

    /**
     * @inheritdoc
     */
    public function createPayment(array $data): array
    {
        Log::info('External Payments TochkaAdapter: createPayment received data', [
            'data' => $data,
        ]);

        $companyId = $data['company_id'] ?? null;

        // Ensure buyer exists before building request (for consumerId lookup)
        if ($companyId && ! empty($data['client_email'])) {
            $this->buyerService->findOrCreate(
                (int) $companyId,
                $data['client_email'],
                $data['client_name'] ?? null,
                $data['client_phone'] ?? null
            );
        }

        $tempPayment = $this->requestBuilder->createPaymentHistory(
            $data,
            [],
            '',
            $companyId
        );

        $requestParams = $this->requestBuilder->buildRequestParams($data, $tempPayment->id, $companyId);

        Log::info('External Payments TochkaAdapter: request params for Tochka API', [
            'request_params' => $requestParams,
            'payment_id'     => $tempPayment->id,
            '-$companyId'  =>  $companyId,
        ]);

        $paymentResponse = $this->requestBuilder->requestPaymentUrl($requestParams, $companyId);

        Log::info('External Payments TochkaAdapter: Tochka API response', [
            'payment_url'   => $paymentResponse['paymentUrl'] ?? null,
            'response_keys' => array_keys($paymentResponse),
        ]);
        $paymentUrl = $paymentResponse['paymentUrl'];
        $responseData = $paymentResponse['response_data'] ?? null;
        $orderId = $requestParams['_orderId'] ?? '';

        $updateData = [
            'order_id'     => $orderId,
            'payment_url'  => $paymentUrl,
            'request_data' => $requestParams,
        ];

        if ($responseData) {
            $updateData['response_data'] = $responseData;
            if (isset($responseData['operationId'])) {
                $updateData['operation_id'] = $responseData['operationId'];
            } elseif (isset($responseData['Data']['operationId'])) {
                $updateData['operation_id'] = $responseData['Data']['operationId'];
            }
        }

        if (! empty($paymentResponse['consumerId'])) {
            $updateData['consumer_id'] = $paymentResponse['consumerId'];
        }

        if (! empty($paymentResponse['paymentLinkId'])) {
            $updateData['payment_link_id'] = $paymentResponse['paymentLinkId'];
        }

        $tempPayment->update($updateData);

        // Save consumerId to buyer when bank returns it
        if (! empty($paymentResponse['consumerId']) && $companyId && ! empty($data['client_email'])) {
            $buyer = $this->buyerService->findOrCreate(
                (int) $companyId,
                $data['client_email'],
                $data['client_name'] ?? null,
                $data['client_phone'] ?? null
            );
            $this->buyerService->updateConsumerId($buyer, $paymentResponse['consumerId']);
        }

        return [
            'payment_id'   => $tempPayment->id,
            'payment_url'  => $paymentUrl,
            'order_id'     => $orderId,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getMinAmount(): float
    {
        $settings = $this->settingsService->getSettings(null);

        return (float) ($settings['min_amount'] ?? config('tochka-payment.min_amount', config('external-payments.min_amount', 1.0)));
    }
}
