<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\ExternalPayments\Models\ExternalSystem;

class WooCommerceOrderStatusService
{
    /**
     * Update WooCommerce order status via REST API
     *
     * @param ExternalSystem $externalSystem
     * @param string $orderId
     * @param string $paymentStatus
     * @return bool
     */
    public function updateOrderStatus(ExternalSystem $externalSystem, string $orderId, string $paymentStatus): bool
    {
        if (empty($externalSystem->woocommerce_site_url)) {
            return false;
        }

        if (empty($externalSystem->woocommerce_consumer_key) || empty($externalSystem->woocommerce_consumer_secret)) {
            return false;
        }

        $woocommerceUrl = rtrim($externalSystem->woocommerce_site_url, '/');
        $apiUrl = $woocommerceUrl . '/wp-json/wc/v3/orders/' . $orderId;

        // Determine order status based on payment status
        $orderStatus = $this->mapPaymentStatusToOrderStatus($paymentStatus, $externalSystem->paid_order_status);

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
                    'external_system_id' => $externalSystem->id,
                ]);
                return true;
            } else {
                Log::error('External Payments WooCommerce: Failed to update order status', [
                    'order_id' => $orderId,
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                    'external_system_id' => $externalSystem->id,
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('External Payments WooCommerce: Exception updating order status', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'external_system_id' => $externalSystem->id,
            ]);
            return false;
        }
    }

    /**
     * Map payment status to WooCommerce order status
     *
     * @param string $paymentStatus
     * @param string|null $paidOrderStatus
     * @return string
     */
    protected function mapPaymentStatusToOrderStatus(string $paymentStatus, ?string $paidOrderStatus = null): string
    {
        // Successful payment statuses
        if (in_array($paymentStatus, ['paid', 'completed'])) {
            return $paidOrderStatus ?? 'processing';
        }

        // Failed payment statuses
        if (in_array($paymentStatus, ['failed', 'cancelled', 'expired'])) {
            return 'failed';
        }

        // Default to pending for unknown statuses
        return 'pending';
    }
}
