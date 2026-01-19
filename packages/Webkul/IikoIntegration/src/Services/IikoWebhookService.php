<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Models\IikoSyncLog;
use Webkul\IikoIntegration\Models\IikoOrderSync;
use Webkul\IikoIntegration\Models\IikoSyncLog;
use Webkul\IikoIntegration\Repositories\IikoOrderSyncRepository;
use Webkul\IikoIntegration\Repositories\IikoSettingRepository;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;

class IikoWebhookService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoOrderSyncRepository $orderSyncRepository,
        protected OrderRepository $orderRepository,
        protected IikoSettingRepository $settingRepository,
        protected IikoSyncLogRepository $syncLogRepository
    ) {}

    /**
     * Process order status update from webhook.
     */
    public function processOrderStatusUpdate(array $data): bool
    {
        try {
            $iikoOrderId = $data['orderId'] ?? null;
            $status = $data['status'] ?? null;

            if (!$iikoOrderId) {
                Log::warning('iiko: Webhook missing orderId', ['data' => $data]);
                return false;
            }

            // Find order sync by iiko order ID
            $sync = $this->orderSyncRepository->findByIikoOrderId($iikoOrderId);

            if (!$sync) {
                Log::warning('iiko: Order sync not found for webhook', ['iiko_order_id' => $iikoOrderId]);
                return false;
            }

            $order = $this->orderRepository->find($sync->order_id);

            if (!$order) {
                Log::error('iiko: Order not found for sync', ['order_id' => $sync->order_id]);
                return false;
            }

            // Map iiko status to our order status
            $mappedStatus = $this->mapIikoStatusToOrderStatus($status);

            if ($mappedStatus && $order->status !== $mappedStatus) {
                $this->orderRepository->update([
                    'status' => $mappedStatus,
                ], $order->id);

                // Fire event for order status update
                event('sales.order.update-status.after', $order);

                Log::info('iiko: Order status updated from webhook', [
                    'order_id'      => $order->id,
                    'iiko_order_id' => $iikoOrderId,
                    'old_status'    => $order->status,
                    'new_status'     => $mappedStatus,
                ]);
            }

            // Log webhook
            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_WEBHOOK,
                'entity_id'    => (string) $order->id,
                'status'       => IikoSyncLog::STATUS_SUCCESS,
                'request_data' => $data,
                'response_data' => ['order_id' => $order->id, 'status' => $mappedStatus],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception processing order status update', [
                'data'    => $data,
                'message' => $e->getMessage(),
            ]);

            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_WEBHOOK,
                'entity_id'    => $data['orderId'] ?? null,
                'status'       => IikoSyncLog::STATUS_ERROR,
                'request_data' => $data,
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process courier assignment from webhook.
     */
    public function processCourierAssignment(array $data): bool
    {
        try {
            $iikoOrderId = $data['orderId'] ?? null;
            $courierId = $data['courierId'] ?? null;
            $courierName = $data['courierName'] ?? null;

            if (!$iikoOrderId) {
                Log::warning('iiko: Webhook missing orderId for courier assignment', ['data' => $data]);
                return false;
            }

            $sync = $this->orderSyncRepository->findByIikoOrderId($iikoOrderId);

            if (!$sync) {
                Log::warning('iiko: Order sync not found for courier assignment', ['iiko_order_id' => $iikoOrderId]);
                return false;
            }

            // Update sync data with courier information
            $syncData = $sync->sync_data ?? [];
            $syncData['courier'] = [
                'id'   => $courierId,
                'name' => $courierName,
            ];

            $this->orderSyncRepository->update([
                'sync_data' => $syncData,
            ], $sync->id);

            Log::info('iiko: Courier assigned from webhook', [
                'order_id'     => $sync->order_id,
                'iiko_order_id' => $iikoOrderId,
                'courier_id'   => $courierId,
                'courier_name' => $courierName,
            ]);

            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_WEBHOOK,
                'entity_id'    => (string) $sync->order_id,
                'status'       => IikoSyncLog::STATUS_SUCCESS,
                'request_data' => $data,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception processing courier assignment', [
                'data'    => $data,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Process order cancellation from webhook.
     */
    public function processOrderCancellation(array $data): bool
    {
        try {
            $iikoOrderId = $data['orderId'] ?? null;
            $cancelCause = $data['cancelCause'] ?? null;

            if (!$iikoOrderId) {
                Log::warning('iiko: Webhook missing orderId for cancellation', ['data' => $data]);
                return false;
            }

            $sync = $this->orderSyncRepository->findByIikoOrderId($iikoOrderId);

            if (!$sync) {
                Log::warning('iiko: Order sync not found for cancellation', ['iiko_order_id' => $iikoOrderId]);
                return false;
            }

            $order = $this->orderRepository->find($sync->order_id);

            if (!$order) {
                Log::error('iiko: Order not found for cancellation', ['order_id' => $sync->order_id]);
                return false;
            }

            // Update order status to cancelled
            if ($order->status !== Order::STATUS_CANCELED) {
                $this->orderRepository->update([
                    'status' => Order::STATUS_CANCELED,
                ], $order->id);

                // Update sync status
                $this->orderSyncRepository->update([
                    'sync_status' => IikoOrderSync::STATUS_CANCELLED,
                ], $sync->id);

                // Fire event
                event('sales.order.cancel.after', $order);

                Log::info('iiko: Order cancelled from webhook', [
                    'order_id'      => $order->id,
                    'iiko_order_id' => $iikoOrderId,
                    'cancel_cause'  => $cancelCause,
                ]);
            }

            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_WEBHOOK,
                'entity_id'    => (string) $order->id,
                'status'       => IikoSyncLog::STATUS_SUCCESS,
                'request_data' => $data,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception processing order cancellation', [
                'data'    => $data,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Map iiko status to order status.
     */
    protected function mapIikoStatusToOrderStatus(?string $iikoStatus): ?string
    {
        if (!$iikoStatus) {
            return null;
        }

        // Map iiko statuses to our order statuses
        // This mapping should be adjusted based on actual iiko status values
        return match (strtolower($iikoStatus)) {
            'new', 'pending' => Order::STATUS_PENDING,
            'in_progress', 'processing' => Order::STATUS_PROCESSING,
            'completed', 'closed' => Order::STATUS_COMPLETED,
            'cancelled', 'canceled' => Order::STATUS_CANCELED,
            default => null,
        };
    }
}
