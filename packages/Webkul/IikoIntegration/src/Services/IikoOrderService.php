<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

            $organizationId = $this->getOrganizationId($order, $channelCode);
            $terminalGroupId = $this->getTerminalGroupId($order, $channelCode);

            if (!$organizationId || !$terminalGroupId) {
                Log::error('iiko: Cannot sync order — missing organization_id or terminal_group_id', [
                    'order_id'          => $order->id,
                    'channel_code'      => $channelCode,
                    'organization_id'   => $organizationId,
                    'terminal_group_id' => $terminalGroupId,
                    'hint'              => 'Configure inventory source iiko_organization_id and iiko_terminal_id, or iiko channel settings / IIKO_ORGANIZATION_ID and IIKO_TERMINAL_GROUP_ID in .env',
                ]);

                return false;
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
                '/api/1/deliveries/create',
                'POST',
                $orderData,
                $channelCode,
                false // Don't log again, we already logged
            );

            $orderInfo = $response['orderInfo'] ?? null;
            $creationStatus = $orderInfo['creationStatus'] ?? null;

            if ($orderInfo && in_array($creationStatus, ['Success', 'InProgress'])) {
                $iikoOrderId = $orderInfo['id'] ?? null;
                $iikoOrderNumber = $orderInfo['order']['number'] ?? null;

                // InProgress means terminal hasn't confirmed yet — save as pending
                $syncStatus = $creationStatus === 'Success'
                    ? IikoOrderSync::STATUS_SYNCED
                    : IikoOrderSync::STATUS_PENDING;

                $this->orderSyncRepository->update([
                    'iiko_order_id'     => $iikoOrderId,
                    'iiko_order_number' => $iikoOrderNumber,
                    'sync_status'       => $syncStatus,
                    'synced_at'         => $creationStatus === 'Success' ? now() : null,
                    'error_message'     => null,
                ], $sync->id);

                // Set order status to processing after successful sync
                if ($creationStatus === 'Success' && $order->status !== Order::STATUS_PROCESSING) {
                    $this->orderRepository->updateOrderStatus($order, Order::STATUS_PROCESSING);

                    Log::info('iiko: Order status set to processing after sync', [
                        'order_id'      => $order->id,
                        'iiko_order_id' => $iikoOrderId,
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

                Log::info('iiko: Order synced', [
                    'order_id'        => $order->id,
                    'iiko_order_id'   => $iikoOrderId,
                    'creationStatus'  => $creationStatus,
                ]);

                return true;
            } else {
                // Parse error from response
                $errorInfo = $orderInfo['errorInfo'] ?? null;
                $errorMessage = $errorInfo['message']
                    ?? $errorInfo['description']
                    ?? $response['description']
                    ?? 'Unknown error';

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
        } catch (\Throwable $e) {
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
     * Prepare order data for iiko API (POST /api/1/deliveries/create).
     *
     * @see docs/iiko-api/deliveries-create.md
     */
    protected function prepareOrderData(Order $order, ?string $channelCode = null): array
    {
        $organizationId = $this->getOrganizationId($order, $channelCode);
        $terminalGroupId = $this->getTerminalGroupId($order, $channelCode);

        $shippingAddress = $order->shipping_address;
        $billingAddress = $order->billing_address;

        // Phone: spec requires "+" prefix, min 8 digits
        $phone = $this->formatPhone($shippingAddress->phone ?? $billingAddress->phone ?? null);

        // Customer (spec: RegularCustomer or OneTimeCustomer)
        $customerName = trim(($order->customer_first_name ?? '') . ' ' . ($order->customer_last_name ?? ''));

        $customerData = $order->customer_id
            ? [
                'type'   => 'regular',
                'name'   => $customerName ?: 'Guest',
                'gender' => 'NotSpecified',
            ]
            : [
                'type'   => 'one-time',
                'name'   => $customerName ?: 'Guest',
                'gender' => 'NotSpecified',
            ];

        if ($order->customer_last_name) {
            $customerData['surname'] = $order->customer_last_name;
        }

        // DeliveryPoint (spec: address.street is an object, address.type is required)
        $deliveryPoint = null;
        if ($shippingAddress) {
            $address = [
                'street' => [
                    'name' => $shippingAddress->address1 ?? '',
                    'city' => $shippingAddress->city ?? '',
                ],
                'house' => $shippingAddress->address2 ?: '0',
                'type'  => 'legacy',
            ];

            if (!empty($shippingAddress->address3)) {
                $address['flat'] = Str::limit($shippingAddress->address3, 100, '');
            }

            if (!empty($shippingAddress->postcode)) {
                $address['index'] = Str::limit($shippingAddress->postcode, 10, '');
            }

            $deliveryPoint = [
                'address' => $address,
            ];
        }

        // Order items (spec: type "Product" required, productId must be iiko UUID)
        $items = [];
        foreach ($order->items as $item) {
            $iikoProductId = $item->product?->additional['iiko_id'] ?? null;

            if (!$iikoProductId) {
                Log::warning('iiko: Product has no iiko_id mapping, skipping item', [
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'sku'        => $item->sku,
                ]);
                continue;
            }

            $iikoSizeId = $item->product?->additional['iiko_size_id'] ?? null;

            $orderItem = [
                'type'      => 'Product',
                'productId' => $iikoProductId,
                'amount'    => (float) $item->qty_ordered,
                'price'     => (float) $item->price,
            ];

            if ($iikoSizeId) {
                $orderItem['productSizeId'] = $iikoSizeId;
            }

            $comment = is_string($item->additional['comment'] ?? null)
                ? Str::limit($item->additional['comment'], 255, '')
                : null;

            if ($comment) {
                $orderItem['comment'] = $comment;
            }

            $items[] = $orderItem;
        }

        if (empty($items)) {
            Log::error('iiko: No items with iiko_id mapping found', ['order_id' => $order->id]);
        }

        // Payment (spec: PaymentRequest)
        $payments = [];
        if ($order->payment) {
            $paymentTypeId = ($organizationId !== null && $organizationId !== '')
                ? $this->getPaymentTypeId($order->payment->method, $organizationId)
                : null;
            $paymentKind = $this->mapPaymentMethod($order->payment->method);

            $paymentData = [
                'paymentTypeKind' => $paymentKind,
                'sum'             => (float) $order->grand_total,
                'paymentTypeId'   => $paymentTypeId,
            ];

            // Online payments are processed externally (money already received)
            if ($paymentKind !== 'Cash') {
                $paymentData['isProcessedExternally'] = true;
            }

            $payments[] = $paymentData;
        }

        // Determine delivery type: courier or pickup
        $orderServiceType = $this->resolveOrderServiceType($order);

        // Build order payload per spec: DeliveryCreateRequest
        $orderPayload = [
            'externalNumber'   => Str::limit($order->increment_id, 50, ''),
            'phone'            => $phone,
            'orderServiceType' => $orderServiceType,
            'comment'          => $order->customer_note ?? null,
            'customer'         => $customerData,
            'items'            => $items,
            'payments'         => $payments,
        ];

        if ($deliveryPoint && $orderServiceType === 'DeliveryByCourier') {
            $orderPayload['deliveryPoint'] = $deliveryPoint;
        }

        return [
            'organizationId'  => $organizationId,
            'terminalGroupId' => $terminalGroupId,
            'order'           => $orderPayload,
        ];
    }

    /**
     * Format phone number to match iiko spec: starts with "+", min 8 digits.
     */
    protected function formatPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Strip everything except digits and leading +
        $digits = preg_replace('/[^\d]/', '', $phone);

        if (strlen($digits) < 8) {
            Log::warning('iiko: Phone number too short', ['phone' => $phone]);
            return null;
        }

        // Ensure + prefix
        return '+' . $digits;
    }

    /**
     * Determine order service type based on shipping method.
     */
    protected function resolveOrderServiceType(Order $order): string
    {
        $shippingMethod = $order->shipping_method ?? '';

        // Common pickup shipping method codes
        $pickupMethods = ['pickup', 'flatrate_pickup', 'store_pickup', 'self_pickup'];

        foreach ($pickupMethods as $method) {
            if (Str::contains($shippingMethod, $method, true)) {
                return 'DeliveryByClient';
            }
        }

        return 'DeliveryByCourier';
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
    protected function getPaymentTypeId(string $paymentMethodCode, ?string $organizationId): ?string
    {
        if ($organizationId === null || $organizationId === '') {
            Log::warning('iiko: Cannot resolve payment type — organization_id is empty', [
                'payment_method_code' => $paymentMethodCode,
            ]);

            return null;
        }

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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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
