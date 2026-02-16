<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\ExternalPayments\Repositories\ExternalPaymentRequestRepository;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;
use Webkul\TochkaPayment\Services\PaymentStatusService;

class WooCommerceCallbackController
{
    public function __construct(
        protected ExternalPaymentRequestRepository $paymentRequestRepository
    ) {}

    /**
     * Handle successful payment callback
     *
     * @param Request $request
     * @return RedirectResponse|View
     */
    public function success(Request $request)
    {
        return $this->handleCallback($request, true);
    }

    /**
     * Handle failed payment callback
     *
     * @param Request $request
     * @return RedirectResponse|View
     */
    public function failure(Request $request)
    {
        return $this->handleCallback($request, false);
    }

    /**
     * Handle payment callback
     *
     * @param Request $request
     * @param bool $isSuccess
     * @return RedirectResponse|View
     */
    protected function handleCallback(Request $request, bool $isSuccess)
    {
        $paymentId = $request->input('payment_id');
        $orderId = $request->input('order_id');
        $operationId = $request->input('operation_id');

        if (!$paymentId && !$operationId) {
            Log::error('External Payments WooCommerce: Missing payment_id or operation_id', [
                'request_data' => $request->all(),
            ]);

            return view('external-payments::woocommerce.error', [
                'message' => __('external-payments::app.woocommerce.missing_parameters'),
            ]);
        }

        try {
            // Find payment
            $payment = null;
            if ($operationId) {
                $payment = TochkaPaymentHistoryProxy::where('operation_id', $operationId)->first();
            } elseif ($paymentId) {
                $payment = TochkaPaymentHistoryProxy::find($paymentId);
            }

            if (!$payment) {
                Log::error('External Payments WooCommerce: Payment not found', [
                    'payment_id' => $paymentId,
                    'operation_id' => $operationId,
                ]);

                return view('external-payments::woocommerce.error', [
                    'message' => __('external-payments::app.woocommerce.payment_not_found'),
                ]);
            }

            // Find external payment request
            $externalRequest = $this->paymentRequestRepository->findByProviderPayment('tochka', $payment->id);

            if (!$externalRequest) {
                Log::error('External Payments WooCommerce: External payment request not found', [
                    'payment_id' => $payment->id,
                ]);

                return view('external-payments::woocommerce.error', [
                    'message' => __('external-payments::app.woocommerce.external_request_not_found'),
                ]);
            }

            $externalSystem = $externalRequest->externalSystem;

            // Check payment status via API
            $statusService = new PaymentStatusService();
            $operationId = $payment->operation_id;

            if (!$operationId) {
                Log::error('External Payments WooCommerce: Operation ID not found', [
                    'payment_id' => $payment->id,
                ]);

                return view('external-payments::woocommerce.error', [
                    'message' => __('external-payments::app.woocommerce.operation_id_not_found'),
                ]);
            }

            try {
                $statusData = $statusService->getOperationStatus($operationId, $payment->company_id);
                $apiStatus = $statusData['status'];
                $paymentStatus = $statusService->mapApiStatusToPaymentStatus($apiStatus);

                // Update payment status if changed
                if ($payment->status !== $paymentStatus) {
                    $payment->update(['status' => $paymentStatus]);
                    $payment->refresh();
                }
            } catch (\Exception $e) {
                Log::error('External Payments WooCommerce: Failed to check payment status', [
                    'payment_id' => $payment->id,
                    'operation_id' => $operationId,
                    'error' => $e->getMessage(),
                ]);

                return view('external-payments::woocommerce.error', [
                    'message' => __('external-payments::app.woocommerce.status_check_failed'),
                ]);
            }

            // Update WooCommerce order status
            if ($externalRequest->external_order_id && $externalSystem->woocommerce_site_url) {
                $this->updateWooCommerceOrderStatus(
                    $externalSystem,
                    $externalRequest->external_order_id,
                    $paymentStatus,
                    $externalSystem->paid_order_status
                );
            }

            // Redirect to WooCommerce order page
            if ($externalRequest->external_order_id && $externalSystem->woocommerce_site_url) {
                $woocommerceUrl = rtrim($externalSystem->woocommerce_site_url, '/');
                $redirectUrl = $woocommerceUrl . '/checkout/order-received/' . $externalRequest->external_order_id . '/';

                return redirect($redirectUrl);
            }

            // If no WooCommerce URL, show success/failure page
            $isPaymentSuccess = in_array($paymentStatus, ['paid', 'completed']);
            return view('external-payments::woocommerce.' . ($isPaymentSuccess ? 'success' : 'failure'), [
                'payment' => $payment,
                'status' => $paymentStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('External Payments WooCommerce: Callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return view('external-payments::woocommerce.error', [
                'message' => __('external-payments::app.woocommerce.callback_error'),
            ]);
        }
    }

    /**
     * Update WooCommerce order status via REST API
     *
     * @param \Webkul\ExternalPayments\Models\ExternalSystem $externalSystem
     * @param string $orderId
     * @param string $paymentStatus
     * @param string $paidStatus
     * @return void
     */
    protected function updateWooCommerceOrderStatus($externalSystem, string $orderId, string $paymentStatus, string $paidStatus): void
    {
        if (empty($externalSystem->woocommerce_site_url) || 
            empty($externalSystem->woocommerce_consumer_key) || 
            empty($externalSystem->woocommerce_consumer_secret)) {
            Log::warning('External Payments WooCommerce: Missing WooCommerce credentials', [
                'external_system_id' => $externalSystem->id,
            ]);
            return;
        }

        $woocommerceUrl = rtrim($externalSystem->woocommerce_site_url, '/');
        $apiUrl = $woocommerceUrl . '/wp-json/wc/v3/orders/' . $orderId;

        // Determine order status based on payment status
        $orderStatus = 'pending';
        if ($paymentStatus === 'paid' || $paymentStatus === 'completed') {
            $orderStatus = $paidStatus;
        } elseif (in_array($paymentStatus, ['failed', 'cancelled', 'expired'])) {
            $orderStatus = 'failed';
        }

        try {
            $response = Http::withBasicAuth(
                $externalSystem->woocommerce_consumer_key,
                $externalSystem->woocommerce_consumer_secret
            )->put($apiUrl, [
                'status' => $orderStatus,
            ]);

            if ($response->successful()) {
                Log::info('External Payments WooCommerce: Order status updated', [
                    'order_id' => $orderId,
                    'status' => $orderStatus,
                    'payment_status' => $paymentStatus,
                ]);
            } else {
                Log::error('External Payments WooCommerce: Failed to update order status', [
                    'order_id' => $orderId,
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('External Payments WooCommerce: Exception updating order status', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
