<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Models\IikoOrderSync;
use Webkul\IikoIntegration\Models\IikoSyncLog;
use Webkul\IikoIntegration\Repositories\IikoOrderSyncRepository;
use Webkul\IikoIntegration\Repositories\IikoPaymentTypeRepository;
use Webkul\IikoIntegration\Repositories\IikoSettingRepository;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;
use Webkul\Inventory\Models\InventorySource;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;

class IikoOrderService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoApiService $apiService,
        protected IikoOrderSyncRepository $orderSyncRepository,
        protected IikoSettingRepository $settingRepository,
        protected IikoSyncLogRepository $syncLogRepository,
        protected OrderRepository $orderRepository,
        protected IikoPaymentTypeRepository $paymentTypeRepository
    ) {}

    /**
     * Sync order to iiko.
     */
    public function syncOrderToIiko(Order $order, ?string $channelCode = null): bool
    {
        try {
            // Check if integration is enabled
            if (!$this->settingRepository->isIntegrationEnabled($channelCode)) {
                Log::info('iiko: Integration is disabled, skipping order sync', ['order_id' => $order->id]);
                return false;
            }

            // Check if order is already synced
            $existingSync = $this->orderSyncRepository->findByOrderId($order->id);
            if ($existingSync && $existingSync->sync_status === IikoOrderSync::STATUS_SYNCED) {
                Log::info('iiko: Order already synced', ['order_id' => $order->id]);
                return true;
            }

            // Prepare order data for iiko
            $orderData = $this->prepareOrderData($order, $channelCode);

            // Create sync record with pending status
            $syncData = [
                'order_id'     => $order->id,
                'sync_status'  => IikoOrderSync::STATUS_PENDING,
                'sync_data'    => $orderData,
            ];

            $sync = $this->orderSyncRepository->createOrUpdate($syncData, $order->id);

            // Log request
            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORDER,
                'entity_id'    => (string) $order->id,
                'status'       => IikoSyncLog::STATUS_PENDING,
                'request_data' => $orderData,
            ]);

            // Send order to iiko
            $response = $this->apiService->makeRequest(
                '/api/1/deliveries/deliveryCreate',
                'POST',
                $orderData,
                $channelCode,
                false // Don't log again, we already logged
            );

            if ($response && isset($response['orderId'])) {
                // Update sync record with success
                $this->orderSyncRepository->update([
                    'iiko_order_id'   => $response['orderId'],
                    'iiko_order_number' => $response['orderNumber'] ?? null,
                    'sync_status'     => IikoOrderSync::STATUS_SYNCED,
                    'synced_at'       => now(),
                    'error_message'   => null,
                ], $sync->id);

                // Set order status to active (processing) after successful sync
                if ($order->status !== Order::STATUS_PROCESSING) {
                    $this->orderRepository->updateOrderStatus($order, Order::STATUS_PROCESSING);
                    
                    Log::info('iiko: Order status set to processing after sync', [
                        'order_id'      => $order->id,
                        'iiko_order_id' => $response['orderId'],
                    ]);
                }

                // Update log
                $this->syncLogRepository->create([
                    'sync_type'    => IikoSyncLog::TYPE_ORDER,
                    'entity_id'     => (string) $order->id,
                    'status'        => IikoSyncLog::STATUS_SUCCESS,
                    'request_data'  => $orderData,
                    'response_data' => $response,
                ]);

                Log::info('iiko: Order synced successfully', [
                    'order_id'      => $order->id,
                    'iiko_order_id' => $response['orderId'],
                ]);

                return true;
            } else {
                // Update sync record with error
                $errorMessage = $response['errorDescription'] ?? 'Unknown error';
                $this->orderSyncRepository->update([
                    'sync_status'  => IikoOrderSync::STATUS_FAILED,
                    'error_message' => $errorMessage,
                ], $sync->id);

                // Update log
                $this->syncLogRepository->create([
                    'sync_type'    => IikoSyncLog::TYPE_ORDER,
                    'entity_id'     => (string) $order->id,
                    'status'        => IikoSyncLog::STATUS_ERROR,
                    'request_data'  => $orderData,
                    'response_data' => $response,
                    'error_message' => $errorMessage,
                ]);

                Log::error('iiko: Failed to sync order', [
                    'order_id' => $order->id,
                    'error'    => $errorMessage,
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('iiko: Exception while syncing order', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            // Update sync record with error
            if (isset($sync)) {
                $this->orderSyncRepository->update([
                    'sync_status'  => IikoOrderSync::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                ], $sync->id);
            }

            return false;
        }
    }

    /**
     * Get inventory source for order.
     * Priority:
     * 1. From shipment if exists
     * 2. First active inventory source from channel with iiko fields filled
     * 3. Fallback to env/config
     */
    protected function getInventorySourceForOrder(Order $order): ?InventorySource
    {
        // Try to get from shipment first
        $shipment = $order->shipments()->first();
        if ($shipment && $shipment->inventory_source_id) {
            $inventorySource = InventorySource::find($shipment->inventory_source_id);
            if ($inventorySource 
                && !empty($inventorySource->iiko_organization_id) 
                && !empty($inventorySource->iiko_terminal_id)) {
                return $inventorySource;
            }
        }

        // Try to get from channel inventory sources
        if ($order->channel) {
            $inventorySources = $order->channel->inventory_sources;
            if ($inventorySources) {
                foreach ($inventorySources as $inventorySource) {
                    if (($inventorySource->status ?? 1) == 1 
                        && !empty($inventorySource->iiko_organization_id) 
                        && !empty($inventorySource->iiko_terminal_id)) {
                        return $inventorySource;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get organization ID from inventory source or fallback to config.
     */
    protected function getOrganizationId(Order $order, ?string $channelCode = null): ?string
    {
        $inventorySource = $this->getInventorySourceForOrder($order);
        
        if ($inventorySource && $inventorySource->iiko_organization_id) {
            return $inventorySource->iiko_organization_id;
        }

        // Fallback to env/config
        return $this->settingRepository->getSettingWithFallback('organization_id', $channelCode);
    }

    /**
     * Get terminal group ID from inventory source or fallback to config.
     */
    protected function getTerminalGroupId(Order $order, ?string $channelCode = null): ?string
    {
        $inventorySource = $this->getInventorySourceForOrder($order);
        
        if ($inventorySource && $inventorySource->iiko_terminal_id) {
            return $inventorySource->iiko_terminal_id;
        }

        // Fallback to env/config
        return $this->settingRepository->getSettingWithFallback('terminal_group_id', $channelCode);
    }

    /**
     * Prepare order data for iiko API.
     */
    protected function prepareOrderData(Order $order, ?string $channelCode = null): array
    {
        $organizationId = $this->getOrganizationId($order, $channelCode);
        $terminalGroupId = $this->getTerminalGroupId($order, $channelCode);

        // Get shipping address
        $shippingAddress = $order->shipping_address;
        $billingAddress = $order->billing_address;

        // Prepare customer data
        $customerData = [
            'name'  => trim(($order->customer_first_name ?? '') . ' ' . ($order->customer_last_name ?? '')),
            'email' => $order->customer_email,
            'phone' => $shippingAddress->phone ?? $billingAddress->phone ?? null,
        ];

        // Prepare delivery point
        $deliveryPoint = null;
        if ($shippingAddress) {
            $deliveryPoint = [
                'address' => [
                    'street'   => $shippingAddress->address1 ?? '',
                    'house'    => $shippingAddress->address2 ?? '',
                    'flat'     => $shippingAddress->address3 ?? null,
                    'entrance' => null,
                    'floor'    => null,
                    'doorphone' => null,
                    'comment'  => $shippingAddress->address2 ?? null,
                ],
                'coordinates' => [
                    'latitude'  => null,
                    'longitude' => null,
                ],
            ];
        }

        // Prepare order items
        $items = [];
        foreach ($order->items as $item) {
            // Note: product_id mapping to iiko product_id should be configured
            // For now, we'll use a placeholder that needs to be mapped
            $items[] = [
                'id'           => $item->product_id, // This should be mapped to iiko product ID
                'productId'    => $item->product_id, // This should be mapped to iiko product ID
                'amount'       => (float) $item->qty_ordered,
                'productName'  => $item->name,
                'modifiers'    => [],
                'price'        => (float) $item->price,
                'positionId'   => null,
                'comment'      => $item->product_options ?? null,
            ];
        }

        // Prepare payment
        $payment = [];
        if ($order->payment) {
            $paymentTypeId = $this->getPaymentTypeId($order->payment->method, $organizationId);
            
            $payment = [
                'paymentTypeKind' => $this->mapPaymentMethod($order->payment->method),
                'sum'             => (float) $order->grand_total,
                'paymentTypeId'   => $paymentTypeId,
                'prepaid'         => false,
            ];
        }

        // Build order data
        $orderData = [
            'organizationId'  => $organizationId,
            'terminalGroupId' => $terminalGroupId,
            'order'           => [
                'externalNumber' => $order->increment_id,
                'phone'          => $customerData['phone'],
                'orderTypeId'    => null, // Should be fetched from order types dictionary
                'deliveryPoint'  => $deliveryPoint,
                'comment'        => $order->customer_note ?? null,
                'customer'       => $customerData,
                'items'          => $items,
                'payments'       => $payment ? [$payment] : [],
                'tips'           => [],
                'discounts'      => [],
                'problem'        => null,
            ],
        ];

        return $orderData;
    }

    /**
     * Map payment method to iiko payment type kind.
     */
    protected function mapPaymentMethod(string $method): string
    {
        return match ($method) {
            'cashondelivery' => 'Cash',
            'moneytransfer'  => 'Card',
            'paypal'         => 'Card',
            default          => 'Card',
        };
    }

    /**
     * Get payment type ID from iiko by payment method code.
     */
    protected function getPaymentTypeId(string $paymentMethodCode, string $organizationId): ?string
    {
        try {
            $paymentType = $this->paymentTypeRepository->findWhere([
                'organization_id'      => $organizationId,
                'payment_method_code'  => $paymentMethodCode,
                'is_active'            => true,
            ])->first();

            if ($paymentType) {
                return $paymentType->iiko_id;
            }

            Log::warning('iiko: Payment method mapping not found', [
                'payment_method_code' => $paymentMethodCode,
                'organization_id'    => $organizationId,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting payment type ID', [
                'payment_method_code' => $paymentMethodCode,
                'organization_id'    => $organizationId,
                'message'            => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Cancel order in iiko.
     */
    public function cancelOrderInIiko(Order $order, ?string $reason = null, ?string $channelCode = null): bool
    {
        try {
            $sync = $this->orderSyncRepository->findByOrderId($order->id);

            if (!$sync || !$sync->iiko_order_id) {
                Log::warning('iiko: Cannot cancel order - not synced', ['order_id' => $order->id]);
                return false;
            }

            $cancelData = [
                'orderId' => $sync->iiko_order_id,
                'organizationId' => $this->getOrganizationId($order, $channelCode),
            ];

            if ($reason) {
                $cancelData['cancelCauseId'] = $reason; // Should be from cancel causes dictionary
            }

            $response = $this->apiService->makeRequest(
                '/api/1/deliveries/close',
                'POST',
                $cancelData,
                $channelCode
            );

            if ($response) {
                $this->orderSyncRepository->update([
                    'sync_status' => IikoOrderSync::STATUS_CANCELLED,
                ], $sync->id);

                Log::info('iiko: Order cancelled successfully', [
                    'order_id'      => $order->id,
                    'iiko_order_id' => $sync->iiko_order_id,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('iiko: Exception while cancelling order', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Update order status in iiko.
     */
    public function updateOrderStatus(Order $order, string $status, ?string $channelCode = null): bool
    {
        // iiko typically manages statuses internally via webhooks
        // This method can be used for specific status updates if needed
        Log::info('iiko: Order status update requested', [
            'order_id' => $order->id,
            'status'   => $status,
        ]);

        return true;
    }
}
