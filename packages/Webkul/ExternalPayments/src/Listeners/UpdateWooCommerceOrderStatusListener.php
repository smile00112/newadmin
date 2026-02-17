<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\ExternalPayments\Repositories\ExternalPaymentRequestRepository;
use Webkul\ExternalPayments\Services\WooCommerceOrderStatusService;
use Webkul\TochkaPayment\Events\PaymentFailed;
use Webkul\TochkaPayment\Events\PaymentSuccess;

class UpdateWooCommerceOrderStatusListener
{
    public function __construct(
        protected ExternalPaymentRequestRepository $paymentRequestRepository,
        protected WooCommerceOrderStatusService $woocommerceService
    ) {}

    /**
     * Handle payment success event
     *
     * @param PaymentSuccess $event
     * @return void
     */
    public function handlePaymentSuccess(PaymentSuccess $event): void
    {
        $this->updateOrderStatus($event->payment, $event->payment->status);
    }

    /**
     * Handle payment failed event
     *
     * @param PaymentFailed $event
     * @return void
     */
    public function handlePaymentFailed(PaymentFailed $event): void
    {
        $this->updateOrderStatus($event->payment, $event->payment->status);
    }

    /**
     * Update WooCommerce order status for payment
     *
     * @param object $payment
     * @param string $paymentStatus
     * @return void
     */
    protected function updateOrderStatus(object $payment, string $paymentStatus): void
    {
        try {
            $paymentId = $payment->id ?? null;
            if ($paymentId === null) {
                return;
            }

            // Find external payment request
            $externalRequest = $this->paymentRequestRepository->findByProviderPayment('tochka', (int) $paymentId);

            if (!$externalRequest) {
                // Payment might not be related to external system, silently return
                return;
            }

            $externalSystem = $externalRequest->externalSystem;

            // Check if WooCommerce is configured
            if (empty($externalSystem->woocommerce_site_url)) {
                // WooCommerce not configured for this external system, silently return
                return;
            }

            // Check if external_order_id exists
            if (empty($externalRequest->external_order_id)) {
                Log::warning('External Payments WooCommerce: Missing external_order_id', [
                    'external_payment_request_id' => $externalRequest->id,
                    'payment_id' => $paymentId,
                ]);
                return;
            }

            // Update WooCommerce order status
            $this->woocommerceService->updateOrderStatus(
                $externalSystem,
                $externalRequest->external_order_id,
                $paymentStatus
            );
        } catch (\Exception $e) {
            // Log error but don't interrupt other listeners
            Log::error('External Payments WooCommerce: Error in UpdateWooCommerceOrderStatusListener', [
                'payment_id' => $payment->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
