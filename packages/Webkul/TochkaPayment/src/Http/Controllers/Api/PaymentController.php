<?php

namespace Webkul\TochkaPayment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Webkul\TochkaPayment\Exceptions\InvalidRequestException;
use Webkul\TochkaPayment\Exceptions\InvalidSignatureException;
use Webkul\TochkaPayment\Exceptions\PaymentNotFoundException;
use Illuminate\Support\Facades\Event;
use Webkul\TochkaPayment\Events\PaymentFailed;
use Webkul\TochkaPayment\Events\PaymentSuccess;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;
use Webkul\TochkaPayment\Services\CallbackHandler;
use Webkul\TochkaPayment\Services\PaymentProcessor;
use Webkul\TochkaPayment\Services\PaymentRequestBuilder;
use Webkul\TochkaPayment\Services\PaymentStatusService;
use Webkul\TochkaPayment\Services\SettingsService;
use Webkul\TochkaPayment\Services\WebhookService;

class PaymentController
{
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
     * Create a new controller instance.
     */
    public function __construct(PaymentRequestBuilder $requestBuilder = null, SettingsService $settingsService = null)
    {
        $this->requestBuilder = $requestBuilder ?? new PaymentRequestBuilder();
        $this->settingsService = $settingsService ?? new SettingsService();
    }

    /**
     * Create a new payment request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:' . config('tochka-payment.min_amount', 1.00),
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:20',
            'external_order_id' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Get company ID from request or authenticated admin
            $companyId = $request->input('company_id');
            if (!$companyId) {
                $admin = auth()->guard('admin')->user();
                $companyId = $admin?->company_id;
            }

            // Get minimum amount from settings
            $settings = $this->settingsService->getSettings($companyId);
            $minAmount = $settings['min_amount'] ?? config('tochka-payment.min_amount', 1.00);

            // Re-validate with correct min amount
            $validator = Validator::make($data, [
                'amount' => 'required|numeric|min:' . $minAmount,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Build request parameters with payment ID (will be created after)
            // We need to create a temporary payment to get ID
            $tempPayment = $this->requestBuilder->createPaymentHistory(
                $data,
                [],
                '',
                $companyId
            );

            // Build request parameters with actual payment ID
            $requestParams = $this->requestBuilder->buildRequestParams($data, $tempPayment->id, $companyId);
            
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

            Log::info('Tochka Payment: Payment created', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'payment_link_id' => $paymentLinkId,
            ]);

            $response = [
                'success' => true,
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'payment_url' => $paymentUrl,
            ];

            if ($paymentLinkId) {
                $response['payment_link_id'] = $paymentLinkId;
            }

            return response()->json($response, 201);
        } catch (\Exception $e) {
            Log::error('Tochka Payment: Failed to create payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle callback from Tochka Bank.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|string
     */
    public function callback(Request $request)
    {
        try {
            $callbackHandler = new CallbackHandler();
            $paymentProcessor = new PaymentProcessor();
            $webhookService = new WebhookService();

            // Process callback
            $paymentData = $callbackHandler->process($request->all());

            Log::info('Tochka Payment: Bank response (callback) to order creation', [
                'payment_id' => $paymentData['payment_id'],
                'order_id' => $paymentData['order_id'],
                'transaction_id' => $paymentData['transaction_id'],
                'amount' => $paymentData['amount'],
                'client_name' => $paymentData['client_name'] ?? null,
            ]);

            // Process successful payment
            $payment = $paymentProcessor->processSuccessfulPayment($paymentData);
            
            // Refresh payment to get updated status
            $payment->refresh();

            // Send webhook notification
            $webhookService->sendPaymentNotification($payment);

            // Dispatch events based on payment status
            // Telegram notifications will be sent by event listeners
            if ($payment->status === \Webkul\TochkaPayment\Models\TochkaPaymentHistory::STATUS_PAID) {
                Event::dispatch(new PaymentSuccess($payment));
            } elseif (in_array($payment->status, [
                \Webkul\TochkaPayment\Models\TochkaPaymentHistory::STATUS_FAILED,
                \Webkul\TochkaPayment\Models\TochkaPaymentHistory::STATUS_CANCELLED
            ])) {
                Event::dispatch(new PaymentFailed($payment));
            }

            // Notify External Payments module (for external systems webhooks)
            Event::dispatch('external_payments.payment.success', [$payment]);

            // Return success response to bank
            return response($callbackHandler->getSuccessResponse($paymentData['transaction_id']), 200)
                ->header('Content-Type', 'text/plain');
        } catch (InvalidRequestException $e) {
            Log::error('Tochka Payment: Invalid callback request', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response($e->getMessage(), $e->getCode())
                ->header('Content-Type', 'text/plain');
        } catch (InvalidSignatureException $e) {
            Log::error('Tochka Payment: Invalid callback signature', [
                'error' => $e->getMessage(),
            ]);

            return response($e->getMessage(), $e->getCode())
                ->header('Content-Type', 'text/plain');
        } catch (PaymentNotFoundException $e) {
            Log::error('Tochka Payment: Payment not found in callback', [
                'error' => $e->getMessage(),
            ]);

            return response($e->getMessage(), $e->getCode())
                ->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            Log::error('Tochka Payment: Callback processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('Internal server error', 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Check payment operation status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request, ?int $id = null): JsonResponse
    {
        try {
            $validator = Validator::make($request->all() + ($id ? ['id' => $id] : []), [
                'operation_id' => 'nullable|string|max:255',
                'payment_id' => 'nullable|integer',
                'id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $operationId = $request->input('operation_id');
            $paymentId = $request->input('payment_id') ?? $id;

            // Get company ID from request or authenticated admin
            $companyId = $request->input('company_id');
            if (!$companyId) {
                $admin = auth()->guard('admin')->user();
                $companyId = $admin?->company_id;
            }

            // Find payment by operation_id or payment_id
            $payment = null;
            if ($operationId) {
                $payment = TochkaPaymentHistoryProxy::findByOperationId($operationId);
            } elseif ($paymentId) {
                $payment = TochkaPaymentHistoryProxy::find($paymentId);
            }

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                ], 404);
            }

            // Use payment's operation_id if available
            if (!$operationId && $payment->operation_id) {
                $operationId = $payment->operation_id;
            }

            if (!$operationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operation ID not found for this payment',
                ], 400);
            }

            // Get company ID from payment if not provided
            if (!$companyId) {
                $companyId = $payment->company_id;
            }

            // Get operation status from API
            $statusService = new PaymentStatusService($this->settingsService);
            $statusData = $statusService->getOperationStatus($operationId, $companyId);

            $apiStatus = $statusData['status'];
            $newPaymentStatus = $statusService->mapApiStatusToPaymentStatus($apiStatus);
            $oldPaymentStatus = $payment->status;

            // Update payment status if changed
            $updateData = [
                'status' => $newPaymentStatus,
                'response_data' => array_merge(
                    $payment->response_data ?? [],
                    ['status_check' => $statusData['response_data']]
                ),
            ];

            // Update operation_id if not set
            if (!$payment->operation_id) {
                $updateData['operation_id'] = $operationId;
            }

            $payment->update($updateData);
            $payment->refresh();

            // Dispatch events only if status changed
            if ($oldPaymentStatus !== $newPaymentStatus) {
                if ($statusService->isSuccessfulStatus($apiStatus)) {
                    Event::dispatch(new PaymentSuccess($payment));
                } elseif ($statusService->isFailedStatus($apiStatus)) {
                    Event::dispatch(new PaymentFailed($payment));
                }
            }

            Log::info('Tochka Payment: Status checked', [
                'payment_id' => $payment->id,
                'operation_id' => $operationId,
                'old_status' => $oldPaymentStatus,
                'new_status' => $newPaymentStatus,
                'api_status' => $apiStatus,
            ]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'operation_id' => $operationId,
                'status' => $newPaymentStatus,
                'api_status' => $apiStatus,
                'status_changed' => $oldPaymentStatus !== $newPaymentStatus,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Tochka Payment: Failed to check payment status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
