<?php

namespace Webkul\TochkaPayment\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;
use Webkul\User\Models\Admin;
use Webkul\User\Repositories\AdminRepository;

class TestPaymentService
{
    /**
     * Admin repository instance.
     *
     * @var \Webkul\User\Repositories\AdminRepository
     */
    protected $adminRepository;

    /**
     * Payment request builder instance.
     *
     * @var \Webkul\TochkaPayment\Services\PaymentRequestBuilder
     */
    protected $requestBuilder;

    /**
     * Settings service instance.
     *
     * @var \Webkul\TochkaPayment\Services\SettingsService
     */
    protected $settingsService;

    /**
     * Create a new test payment service instance.
     */
    public function __construct(
        AdminRepository $adminRepository = null,
        PaymentRequestBuilder $requestBuilder = null,
        SettingsService $settingsService = null
    ) {
        $this->adminRepository = $adminRepository ?? app(AdminRepository::class);
        $this->requestBuilder = $requestBuilder ?? new PaymentRequestBuilder();
        $this->settingsService = $settingsService ?? new SettingsService();
    }

    /**
     * Process test payment creation.
     *
     * @param  array  $data
     * @return array
     * @throws \Exception
     */
    public function processTestPayment(array $data): array
    {
        try {
            // Find or create admin user
            $admin = $this->findOrCreateAdmin(
                $data['email'],
                $data['name'],
                $data['phone'] ?? null,
                $data['company_id'] ?? null
            );

            // Get company ID
            $companyId = $data['company_id'] ?? $admin->company_id;

            // Get minimum amount from settings
            $settings = $this->settingsService->getSettings($companyId);
            $minAmount = $settings['min_amount'] ?? config('tochka-payment.min_amount', 1.00);

            // Validate amount
            if ((float) $data['amount'] < $minAmount) {
                throw new \Exception("Amount must be at least {$minAmount}");
            }

            // Ensure buyer exists before building request (for consumerId lookup)
            if ($companyId && ! empty($data['email'])) {
                $buyerService = app(TochkaPaymentBuyerService::class);
                $buyerService->findOrCreate(
                    (int) $companyId,
                    $data['email'],
                    $data['name'] ?? null,
                    $data['phone'] ?? null
                );
            }

            // Prepare payment data (customerCode and merchantId are taken from settings in buildRequestParams)
            $paymentData = [
                'amount' => $data['amount'],
                'client_name' => $data['name'],
                'client_email' => $data['email'],
                'client_phone' => $data['phone'] ?? '',
                'product_name' => $data['purpose'] ?? 'Тестовый платеж',
                'external_order_id' => $data['external_order_id'] ?? null,
            ];

            // Create temporary payment history
            $tempPayment = $this->requestBuilder->createPaymentHistory(
                $paymentData,
                [],
                '',
                $companyId
            );

            // Build request parameters with admin ID as customerCode
            $requestParams = $this->requestBuilder->buildRequestParams(
                $paymentData,
                $tempPayment->id,
                $companyId
            );

            // Request payment URL from Tochka API
            $paymentResponse = $this->requestBuilder->requestPaymentUrl($requestParams, $companyId);
            $paymentUrl = $paymentResponse['paymentUrl'];
            $paymentLinkId = $paymentResponse['paymentLinkId'] ?? null;
            $consumerId = $paymentResponse['consumerId'] ?? null;
            $responseData = $paymentResponse['response_data'] ?? null;

            // Extract order ID from request params
            $orderId = $requestParams['_orderId'] ?? '';

            // Update payment with correct URL, request data, and response data
            $updateData = [
                'order_id' => $orderId,
                'payment_url' => $paymentUrl,
                'request_data' => $requestParams,
            ];

            // Store response data if available
            if ($responseData) {
                $updateData['response_data'] = $responseData;

                // Extract operationId if available
                if (isset($responseData['operationId'])) {
                    $updateData['operation_id'] = $responseData['operationId'];
                } elseif (isset($responseData['Data']['operationId'])) {
                    $updateData['operation_id'] = $responseData['Data']['operationId'];
                }
            }

            // Store consumerId if available
            if ($consumerId) {
                $updateData['consumer_id'] = $consumerId;
            }

            // Store paymentLinkId if available
            if ($paymentLinkId) {
                $updateData['payment_link_id'] = $paymentLinkId;
            }

            $tempPayment->update($updateData);
            $payment = $tempPayment;

            // Save consumerId to buyer when bank returns it
            if ($consumerId && $companyId && ! empty($paymentData['client_email'])) {
                $buyerService = app(TochkaPaymentBuyerService::class);
                $buyer = $buyerService->findOrCreate(
                    (int) $companyId,
                    $paymentData['client_email'],
                    $paymentData['client_name'] ?? null,
                    $paymentData['client_phone'] ?? null
                );
                $buyerService->updateConsumerId($buyer, $consumerId);
            }

            Log::info('Tochka Payment: Test payment created', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'admin_id' => $admin->id,
                'company_id' => $companyId,
                'payment_link_id' => $paymentLinkId,
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'payment_url' => $paymentUrl,
                'admin' => $admin,
            ];
        } catch (\Exception $e) {
            Log::error('Tochka Payment: Failed to create test payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Find or create admin user by email.
     *
     * @param  string  $email
     * @param  string  $name
     * @param  string|null  $phone
     * @param  int|null  $companyId
     * @return \Webkul\User\Models\Admin
     */
    public function findOrCreateAdmin(string $email, string $name, ?string $phone = null, ?int $companyId = null): Admin
    {
        // Try to find existing admin by email
        $admin = Admin::where('email', $email)->first();

        if ($admin) {
            Log::info('Tochka Payment: Admin found', [
                'admin_id' => $admin->id,
                'email' => $email,
            ]);

            return $admin;
        }

        // Create new admin user
        $adminData = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(16)), // Generate random password
            'role_id' => 1, // Default role
            'status' => 1, // Active
            'api_token' => Str::random(80),
        ];

        // Add company_id if provided
        if ($companyId) {
            $adminData['company_id'] = $companyId;
        }

        $admin = $this->adminRepository->create($adminData);

        Log::info('Tochka Payment: Admin created', [
            'admin_id' => $admin->id,
            'email' => $email,
            'company_id' => $companyId,
        ]);

        return $admin;
    }
}
