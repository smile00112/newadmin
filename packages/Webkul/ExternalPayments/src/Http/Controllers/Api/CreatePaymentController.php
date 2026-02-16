<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\ExternalPayments\Models\ExternalSystem;
use Webkul\ExternalPayments\Repositories\ExternalPaymentRequestRepository;
use Webkul\ExternalPayments\Repositories\ExternalSystemRepository;
use Webkul\ExternalPayments\Services\PaymentProviderRegistry;

class CreatePaymentController
{
    public function __construct(
        protected ExternalSystemRepository $externalSystemRepository,
        protected ExternalPaymentRequestRepository $paymentRequestRepository,
        protected PaymentProviderRegistry $providerRegistry,
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /** @var ExternalSystem|null $externalSystem */
        $externalSystem = $request->attributes->get('external_system');

        if (! $externalSystem) {
            return response()->json([
                'success' => false,
                'message' => __('external-payments::app.api.token_invalid'),
            ], 401);
        }

        $providerKey = $request->input('payment_provider');
        if (! $providerKey) {
            $providerKey = $externalSystem->default_provider;
        }

        if (! $providerKey) {
            return response()->json([
                'success' => false,
                'message' => __('external-payments::app.api.no_default_provider'),
            ], 422);
        }

        $allowed = $externalSystem->paymentProviders()
            ->where('payment_provider', $providerKey)
            ->exists();

        if (! $allowed) {
            return response()->json([
                'success' => false,
                'message' => __('external-payments::app.api.provider_not_allowed'),
            ], 422);
        }

        if (! $this->providerRegistry->has($providerKey)) {
            return response()->json([
                'success' => false,
                'message' => __('external-payments::app.api.unknown_provider'),
            ], 422);
        }

        $adapter = $this->providerRegistry->get($providerKey);
        $minAmount = $adapter->getMinAmount();

        $validator = Validator::make($request->all(), [
            'amount'            => 'required|numeric|min:'.$minAmount,
            'client_name'       => 'required|string|max:255',
            'client_email'      => 'required|email|max:255',
            'client_phone'      => 'required|string|max:20',
            'external_order_id' => 'nullable|string|max:255',
            'product_name'      => 'nullable|string|max:255',
            'payment_provider'  => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('external-payments::app.api.validation_failed'),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        unset($data['payment_provider']);

        // Add company_id from external system
        if ($externalSystem->company_id) {
            $data['company_id'] = $externalSystem->company_id;
        }

        // Find or create customer by email
        $customer = $this->customerRepository->findOneByField('email', $data['client_email']);
        
        if (!$customer && $externalSystem->company_id) {
            // Parse name into first_name and last_name
            $nameParts = $this->parseName($data['client_name']);
            
            $customer = $this->customerRepository->create([
                'first_name' => $nameParts['first_name'],
                'last_name' => $nameParts['last_name'],
                'email' => $data['client_email'],
                'phone' => $data['client_phone'],
                'status' => 1,
                'is_verified' => 1,
                'channel_id' => core()->getCurrentChannel()->id,
                'customer_group_id' => $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id,
            ]);

            Log::info('External Payments: Customer created', [
                'customer_id' => $customer->id,
                'email' => $data['client_email'],
                'external_system_id' => $externalSystem->id,
            ]);
        }

        try {
            $result = $adapter->createPayment($data);

            $this->paymentRequestRepository->create([
                'external_system_id'     => $externalSystem->id,
                'payment_provider'       => $providerKey,
                'provider_payment_id'   => $result['payment_id'],
                'provider_order_id'     => $result['order_id'],
                'external_order_id'     => $data['external_order_id'] ?? null,
                'status'                => 'pending',
            ]);

            Log::info('External Payments: Payment created', [
                'external_system_id' => $externalSystem->id,
                'provider'           => $providerKey,
                'payment_id'         => $result['payment_id'],
            ]);

            return response()->json([
                'success'       => true,
                'payment_id'    => $result['payment_id'],
                'order_id'       => $result['order_id'],
                'payment_url'    => $result['payment_url'],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('External Payments: Failed to create payment', [
                'external_system_id' => $externalSystem->id,
                'error'              => $e->getMessage(),
                'trace'              => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('external-payments::app.api.create_failed'),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse name into first_name and last_name
     *
     * @param string $name
     * @return array
     */
    protected function parseName(string $name): array
    {
        $name = trim($name);
        $lastName = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $firstName = trim(preg_replace('#'.$lastName.'#', '', $name));

        return [
            'first_name' => $firstName ?: $name,
            'last_name' => $lastName,
        ];
    }
}
