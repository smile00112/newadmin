<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Checkout\Facades\Cart;
use Webkul\PushNotification\Models\OrderLiveActivityToken;
use Webkul\RestApi\Http\Resources\V1\Shop\Checkout\CartResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Sales\OrderListResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Sales\OrderResource;
use Webkul\RestApi\Jobs\WarmCustomerOrdersCacheJob;
use Webkul\RestApi\Services\CustomerOrdersCache;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;

class OrderController extends CustomerController
{
    /**
     * @var \Webkul\Sales\Repositories\InvoiceRepository
     */
    protected InvoiceRepository $invoiceRepository;

    /**
     * @var \Webkul\Sales\Repositories\OrderRepository
     */
    protected OrderRepository $orderRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return OrderRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return OrderResource::class;
    }

    /**
     * Confirm payment for the specified order using Alfabank gateway.
     */
    public function confirmPayment(Request $request, int $id): \Illuminate\Http\Response
    {
        $validated = $request->validate([
            'gateway_order_id' => 'required|string',
        ]);

        $customer = $this->resolveShopUser($request);
        $alfabankLogChannel = config('alfabank-payment.log_channel', 'daily');
        $gatewayOrderId = $validated['gateway_order_id'];

        Log::channel($alfabankLogChannel)->info('Alfabank confirmPayment: request received', [
            'customer_id'     => $customer->id,
            'order_id'        => $id,
            'gateway_order_id'=> $gatewayOrderId,
        ]);

        /** @var \Webkul\Sales\Models\Order|null $order */
        $order = $customer->orders()->with('payment', 'items')->find($id);

        if (! $order) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.not-found'),
            ], 404);
        }

        if ($order->payment?->method !== 'alfabank') {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.invalid-payment-method'),
            ], 422);
        }

        if (! in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PENDING_PAYMENT, Order::STATUS_FRAUD, Order::STATUS_CANCELED])) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.invalid-status-for-payment'),
            ], 422);
        }

        try {
            $alfabankApi = app(\Webkul\AlfabankPayment\Services\AlfabankApiService::class);

            // Note: Alfa SDK/service logs full request/response if enabled.
            // Here we log just the endpoint-level intent and expected totals for easier tracing.
            $expectedAmount = (int) round($order->grand_total * 100);
            Log::channel($alfabankLogChannel)->info('Alfabank confirmPayment: checking gateway order', [
                'order_id'         => $order->id,
                'gateway_order_id' => $gatewayOrderId,
                'expected_amount'  => $expectedAmount,
                'order_status'      => $order->status,
                'payment_method'    => $order->payment?->method,
            ]);

            $response = $alfabankApi->getOrderStatus($gatewayOrderId);

            if (isset($response['errorCode']) && $response['errorCode'] !== '0') {
                Log::channel($alfabankLogChannel)->warning('Alfabank confirmPayment: gateway returned error', [
                    'order_id'        => $order->id,
                    'gateway_order_id'=> $gatewayOrderId,
                    'errorCode'       => $response['errorCode'] ?? null,
                    'errorMessage'    => $response['errorMessage'] ?? null,
                ]);

                $this->storeFailedCheckReason($order, 'gateway_error', $response);

                return response([
                    'message' => trans('rest-api::app.shop.sales.orders.error.payment-not-confirmed'),
                ], 422);
            }

            $orderStatus = $response['orderStatus'] ?? null;
            $amount = isset($response['amount']) ? (int) $response['amount'] : null;
            $amountDiff = $amount !== null ? $amount - $expectedAmount : null;

            Log::channel($alfabankLogChannel)->info('Alfabank confirmPayment: gateway response summary', [
                'order_id'         => $order->id,
                'gateway_order_id' => $gatewayOrderId,
                'orderStatus'      => $orderStatus,
                'amount'           => $amount,
                'expected_amount' => $expectedAmount,
                'amount_diff'     => $amountDiff,
            ]);

            if ($orderStatus !== 2 || $amount !== $expectedAmount) {
                Log::channel($alfabankLogChannel)->warning('Alfabank confirmPayment: status or amount mismatch', [
                    'order_id'         => $order->id,
                    'gateway_order_id' => $gatewayOrderId,
                    'orderStatus'      => $orderStatus,
                    'amount'           => $amount,
                    'expected_amount'  => $expectedAmount,
                ]);

                $this->storeFailedCheckReason($order, 'status_or_amount_mismatch', $response);

                return response([
                    'message' => trans('rest-api::app.shop.sales.orders.error.payment-not-confirmed'),
                ], 422);
            }

            $invoiceId = null;

            DB::beginTransaction();

            try {
                $invoiceData = [
                    'order_id' => $order->id,
                    'invoice'  => [
                        'items' => [],
                    ],
                ];

                foreach ($order->items as $item) {
                    if ($item->qty_to_invoice > 0) {
                        $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
                    }
                }

                if (! empty($invoiceData['invoice']['items']) && $this->invoiceRepository->haveProductToInvoice($invoiceData)) {
                    if (! $this->invoiceRepository->isValidQuantity($invoiceData)) {
                        throw new \Exception('Invalid invoice quantities for order ' . $order->id);
                    }

                    $invoice = $this->invoiceRepository->create($invoiceData);

                    $invoiceId = $invoice->id;
                }

                $orderStatusPaid = core()->getConfigData('sales.payment_methods.alfabank.order_status_paid') ?? Order::STATUS_PROCESSING;

                if ($order->status !== $orderStatusPaid) {
                    $order->status = $orderStatusPaid;
                }

                $additional = $order->additional ?? [];
                $additional['alfabank_order_id'] = $gatewayOrderId;

                if (isset($response['authRefNum'])) {
                    $additional['transaction_id'] = $response['authRefNum'];
                }

                unset($additional['alfabank_last_failed_check']);

                $order->additional = $additional;

                $order->save();

                if ($order->payment) {
                    $paymentAdditional = $order->payment->additional ?? [];
                    $paymentAdditional['alfabank_order_id'] = $gatewayOrderId;

                    if (isset($response['authRefNum'])) {
                        $paymentAdditional['transaction_id'] = $response['authRefNum'];
                    }

                    $order->payment->additional = $paymentAdditional;
                    $order->payment->save();
                }

                DB::commit();

                Log::channel($alfabankLogChannel)->info('Alfabank confirmPayment: success', [
                    'order_id'          => $order->id,
                    'gateway_order_id' => $gatewayOrderId,
                    'invoice_id'       => $invoiceId,
                    'order_status'     => $orderStatusPaid,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::channel($alfabankLogChannel)->error('Alfabank confirmPayment: failed to create invoice or update order', [
                    'order_id'         => $order->id,
                    'gateway_order_id' => $gatewayOrderId,
                    'exception'        => $e->getMessage(),
                ]);

                return response([
                    'message' => trans('rest-api::app.shop.sales.orders.error.payment-processing-failed'),
                ], 500);
            }

            $order->refresh();

            $resourceClass = $this->resource();

            return response([
                'data' => [
                    'order'      => (new $resourceClass($order))->resolve($request),
                    'invoice_id' => $invoiceId,
                ],
                'message' => trans('rest-api::app.shop.sales.orders.payment-confirmed'),
            ]);
        } catch (\Exception $e) {
            Log::error('Alfabank confirmPayment: exception', [
                'order_id'         => $order->id ?? null,
                'gateway_order_id' => $gatewayOrderId ?? null,
                'exception'        => $e->getMessage(),
            ]);

            return response([
                'message' => trans('rest-api::app.shop.errors.report-success'),
            ], 500);
        }
    }

    /**
     * Store reason of failed payment confirmation check in order additional data.
     */
    protected function storeFailedCheckReason(Order $order, string $reasonCode, array $gatewayResponse): void
    {
        try {
            $additional = $order->additional ?? [];

            $additional['alfabank_last_failed_check'] = [
                'reason'   => $reasonCode,
                'response' => [
                    'orderStatus'            => $gatewayResponse['orderStatus'] ?? null,
                    'actionCode'             => $gatewayResponse['actionCode'] ?? null,
                    'actionCodeDescription'  => $gatewayResponse['actionCodeDescription'] ?? null,
                    'amount'                 => $gatewayResponse['amount'] ?? null,
                    'currency'               => $gatewayResponse['currency'] ?? null,
                    'errorCode'              => $gatewayResponse['errorCode'] ?? null,
                    'errorMessage'           => $gatewayResponse['errorMessage'] ?? null,
                ],
                'checked_at' => now()->toIso8601String(),
            ];

            $order->additional = $additional;
            $order->save();
        } catch (\Exception $e) {
            Log::error('Alfabank confirmPayment: failed to store failed check reason', [
                'order_id'  => $order->id,
                'reason'    => $reasonCode,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * List resource class name (lightweight for listing).
     */
    protected function listResource(): string
    {
        return OrderListResource::class;
    }

    /**
     * Returns a listing of the resource (optimized with eager loading, lightweight resource and caching).
     *
     * @return \Illuminate\Http\Response
     */
    public function allResources(Request $request)
    {
        $customerId = $this->resolveShopUser($request)->id;

        $params = $request->only(['page', 'limit', 'pagination', 'sort', 'order'])
            + $request->except(array_merge($this->requestException, ['token']));

        $cacheKey = CustomerOrdersCache::key($customerId, ['all' => $params]);

        $data = Cache::remember($cacheKey, CustomerOrdersCache::ttl(), function () use ($request) {
            $query = $this->getRepositoryInstance()->scopeQuery(function ($query) use ($request) {
                if ($this->isAuthorized()) {
                    $query = $query->where('customer_id', $this->resolveShopUser($request)->id);
                }

                foreach ($request->except($this->requestException) as $input => $value) {
                    $query = $query->whereIn($input, array_map('trim', explode(',', $value)));
                }

                if ($sort = $request->input('sort')) {
                    $query = $query->orderBy($sort, $request->input('order') ?? 'desc');
                } else {
                    $query = $query->orderBy('id', 'desc');
                }

                return $query;
            });

            $query->with([
                'items.order',
                'items.product.images',
                'items.product.parent.images',
            ]);

            if (is_null($request->input('pagination')) || $request->input('pagination')) {
                $results = $query->paginate($request->input('limit') ?? 10);
            } else {
                $results = $query->get();
            }

            $resourceClass = $this->listResource();

            //return $resourceClass::collection($results)->resolve($request);
            return json_encode($resourceClass::collection($results)->resolve($request), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });

        //return response()->json($data);
        return api_stream_json($data, 'orders_'. $cacheKey .'.json');

    }

    /**
     * Cancel customer's order.
     */
    public function cancel(Request $request, int $id): \Illuminate\Http\Response
    {
        $customer = $this->resolveShopUser($request);
        $order = $customer->orders()->with('payment', 'items')->find($id);

        if (! $order) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.not-found'),
            ], 404);
        }

        $gatewayFallbackReason = null;

        if ($order->payment?->method === 'alfabank') {
            $alfabankLogChannel = config('alfabank-payment.log_channel', 'daily');
            $bankOrderId = $this->resolveAlfabankOrderId($order);

            if (! $bankOrderId) {
                Log::channel($alfabankLogChannel)->warning('Alfabank customer cancel: falling back to local cancel', [
                    'order_id' => $order->id,
                    'reason'   => 'missing_gateway_order_id',
                ]);
                $gatewayFallbackReason = 'missing_gateway_order_id';
            } else {
                $isPaidInGateway = false;
                $gatewayFailed = false;

                try {
                    $gatewayStatus = $this->fetchAlfabankOrderStatus($bankOrderId, $order);
                    $isPaidInGateway = $this->isAlfabankOrderPaid($gatewayStatus);
                } catch (\RuntimeException $e) {
                    Log::channel($alfabankLogChannel)->warning('Alfabank customer cancel: status check failed, falling back to local cancel', [
                        'order_id'         => $order->id,
                        'gateway_order_id' => $bankOrderId,
                        'reason'           => 'status_check_failed',
                        'message'          => $e->getMessage(),
                    ]);
                    $gatewayFallbackReason = 'status_check_failed';
                    $gatewayFailed = true;
                } catch (\Throwable $e) {
                    Log::channel($alfabankLogChannel)->error('Alfabank customer cancel: unexpected error during status check, falling back to local cancel', [
                        'order_id'        => $order->id,
                        'gateway_order_id' => $bankOrderId,
                        'reason'          => 'unexpected',
                        'message'         => $e->getMessage(),
                        'exception_class' => get_class($e),
                    ]);
                    $gatewayFallbackReason = 'unexpected';
                    $gatewayFailed = true;
                }

                if (! $gatewayFailed && $isPaidInGateway) {
                    try {
                        $gatewayResponse = $this->processGatewayFullRefund($order, $bankOrderId);

                        if ($order->status !== Order::STATUS_CANCELED) {
                            $this->orderRepository->update([
                                'status' => Order::STATUS_CANCELED,
                            ], $order->id);

                            $order->refresh();
                        }

                        return response([
                            'message' => 'Order has been canceled with full refund request.',
                            'data'    => [
                                'order_id'         => $order->id,
                                'order_status'     => $order->status,
                                'gateway_order_id' => $bankOrderId,
                                'gateway_action'   => 'refund',
                                'gateway_response' => $gatewayResponse,
                            ],
                        ]);
                    } catch (\RuntimeException $e) {
                        Log::channel($alfabankLogChannel)->warning('Alfabank customer cancel: refund failed, falling back to local cancel', [
                            'order_id'         => $order->id,
                            'gateway_order_id' => $bankOrderId,
                            'reason'           => 'refund_failed',
                            'message'          => $e->getMessage(),
                        ]);
                        $gatewayFallbackReason = 'refund_failed';
                    } catch (\Throwable $e) {
                        Log::channel($alfabankLogChannel)->error('Alfabank customer cancel: unexpected error during refund, falling back to local cancel', [
                            'order_id'        => $order->id,
                            'gateway_order_id' => $bankOrderId,
                            'reason'          => 'unexpected',
                            'message'         => $e->getMessage(),
                            'exception_class' => get_class($e),
                        ]);
                        $gatewayFallbackReason = 'unexpected';
                    }
                }
            }
        }

        if ($gatewayFallbackReason !== null) {
            return $this->cancelLocallyAfterGatewayError($order, $gatewayFallbackReason);
        }

        if ($this->getRepositoryInstance()->cancel($order)) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.cancel'),
                'data'    => [
                    'order_id'       => $order->id,
                    'gateway_action' => $order->payment?->method === 'alfabank' ? 'cancel' : 'none',
                ],
            ]);
        }

        // Определяем причину, почему заказ не может быть отменен
        $reason = $this->getCancelErrorReason($order);

        return response([
            'message' => trans('rest-api::app.shop.sales.orders.error.cancel-error'),
            'reason'  => $reason,
        ], 422);
    }

    /**
     * Attempt local order cancellation after an Alfabank gateway error.
     *
     * Tries full repository cancel first (stock return, events).
     * If that is unavailable but the order is not in a terminal state,
     * forces a status-only update to STATUS_CANCELED — matching the same
     * approach used on successful gateway refund. Every outcome is logged.
     */
    protected function cancelLocallyAfterGatewayError(Order $order, string $fallbackReason): \Illuminate\Http\Response
    {
        $alfabankLogChannel = config('alfabank-payment.log_channel', 'daily');

        if ($this->getRepositoryInstance()->cancel($order)) {
            $order->refresh();

            Log::channel($alfabankLogChannel)->info('Alfabank customer cancel: local cancel succeeded after gateway error', [
                'order_id'                => $order->id,
                'gateway_fallback_reason' => $fallbackReason,
            ]);

            return response([
                'message' => trans('rest-api::app.shop.sales.orders.cancel'),
                'data'    => [
                    'order_id'                => $order->id,
                    'gateway_action'          => 'local_fallback',
                    'gateway_fallback_reason' => $fallbackReason,
                ],
            ]);
        }

        // Full cancel not possible (e.g. invoiced items).
        // Force status-only update unless order is already in a terminal state.
        if (! in_array($order->status, [Order::STATUS_CLOSED, Order::STATUS_FRAUD, Order::STATUS_CANCELED])) {
            $this->orderRepository->update([
                'status' => Order::STATUS_CANCELED,
            ], $order->id);

            $order->refresh();

            Log::channel($alfabankLogChannel)->warning('Alfabank customer cancel: forced status-only cancel after gateway error', [
                'order_id'                => $order->id,
                'order_status'            => $order->status,
                'gateway_fallback_reason' => $fallbackReason,
            ]);

            return response([
                'message' => trans('rest-api::app.shop.sales.orders.cancel'),
                'data'    => [
                    'order_id'                => $order->id,
                    'order_status'            => $order->status,
                    'gateway_action'          => 'local_fallback',
                    'gateway_fallback_reason' => $fallbackReason,
                ],
            ]);
        }

        $reason = $this->getCancelErrorReason($order);

        return response([
            'message' => trans('rest-api::app.shop.sales.orders.error.cancel-error'),
            'reason'  => $reason,
        ], 422);
    }

    /**
     * Bind table number to customer's order.
     */
    public function bindTable(Request $request): \Illuminate\Http\Response
    {
        $validatedData = $request->validate([
            'order_id'     => ['required', 'integer', 'exists:orders,id'],
            'table_number' => ['required', 'integer', 'min:1'],
        ]);

        $customer = $this->resolveShopUser($request);
        $order = $customer->orders()->find($validatedData['order_id']);

        if (! $order) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.not-found'),
            ], 404);
        }

        $order->update(['table_number' => $validatedData['table_number']]);

        $this->refreshOrdersCacheForCustomer($order, (int) $customer->id);

        $resourceClassName = $this->resource();

        return response([
            'data'    => new $resourceClassName($order->fresh()),
            'message' => trans('rest-api::app.shop.sales.orders.bind-table-success'),
        ]);
    }

    /**
     * Unbind table number from customer's order.
     */
    public function unbindTable(Request $request): \Illuminate\Http\Response
    {
        $validatedData = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $customer = $this->resolveShopUser($request);
        $order = $customer->orders()->find($validatedData['order_id']);

        if (! $order) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.not-found'),
            ], 404);
        }

        $order->update(['table_number' => null]);

        $this->refreshOrdersCacheForCustomer($order, (int) $customer->id);

        $resourceClassName = $this->resource();

        return response([
            'data'    => new $resourceClassName($order->fresh()),
            'message' => trans('rest-api::app.shop.sales.orders.unbind-table-success'),
        ]);
    }

    /**
     * Invalidate and warm customer orders cache for the order's channel.
     */
    private function refreshOrdersCacheForCustomer(Order $order, int $customerId): void
    {
        CustomerOrdersCache::invalidate($customerId);

        $order->loadMissing('channel');
        $channelCode = (string) ($order->channel?->code ?? core()->getDefaultChannelCode() ?? '');

        WarmCustomerOrdersCacheJob::dispatch($customerId, $channelCode);
    }

    /**
     * Refund customer's paid order via Alfabank gateway.
     */
    public function refund(Request $request, int $id): \Illuminate\Http\Response
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
        ]);

        $customer = $this->resolveShopUser($request);
        $alfabankLogChannel = config('alfabank-payment.log_channel', 'daily');

        /** @var \Webkul\Sales\Models\Order|null $order */
        $order = $customer->orders()->with('payment')->find($id);

        if (! $order) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.not-found'),
            ], 404);
        }

        if ($order->payment?->method !== 'alfabank') {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.invalid-payment-method'),
            ], 422);
        }

        $bankOrderId = $this->resolveAlfabankOrderId($order);

        if (! $bankOrderId) {
            return response([
                'message' => 'Refund is unavailable: missing Alfabank gateway order id.',
            ], 422);
        }

        $requestedAmount = $validated['amount'] ?? null;
        $gatewayAmount = $requestedAmount === null
            ? 0
            : (int) round(((float) $requestedAmount) * 100);

        if ($requestedAmount !== null && $gatewayAmount <= 0) {
            return response([
                'message' => 'Refund amount must be greater than zero.',
            ], 422);
        }

        $currency = $this->resolveNumericCurrencyCode((string) $order->order_currency_code);
        $language = app()->getLocale() ?: 'ru';

        try {
            Log::channel($alfabankLogChannel)->info('Alfabank customer refund: request', [
                'customer_id'      => $customer->id,
                'order_id'         => $order->id,
                'gateway_order_id' => $bankOrderId,
                'requested_amount' => $requestedAmount,
                'gateway_amount'   => $gatewayAmount,
            ]);

            $alfabankApi = app(\Webkul\AlfabankPayment\Services\AlfabankApiService::class);
            $response = $alfabankApi->refundOrder($bankOrderId, $gatewayAmount, $language, $currency);

            if (isset($response['errorCode']) && (string) $response['errorCode'] !== '0') {
                Log::channel($alfabankLogChannel)->warning('Alfabank customer refund: gateway error', [
                    'customer_id'      => $customer->id,
                    'order_id'         => $order->id,
                    'gateway_order_id' => $bankOrderId,
                    'errorCode'        => $response['errorCode'] ?? null,
                    'errorMessage'     => $response['errorMessage'] ?? null,
                ]);

                return response([
                    'message' => 'Alfabank refund error: ' . ($response['errorMessage'] ?? 'Unknown gateway error'),
                ], 422);
            }

            Log::channel($alfabankLogChannel)->info('Alfabank customer refund: success', [
                'customer_id'      => $customer->id,
                'order_id'         => $order->id,
                'gateway_order_id' => $bankOrderId,
                'requested_amount' => $requestedAmount,
                'gateway_amount'   => $gatewayAmount,
            ]);

            if ($order->status !== Order::STATUS_CANCELED) {
                $this->orderRepository->update([
                    'status' => Order::STATUS_CANCELED,
                ], $order->id);

                $order->refresh();
            }

            return response([
                'message' => 'Refund request has been sent successfully.',
                'data'    => [
                    'order_id'         => $order->id,
                    'order_status'     => $order->status,
                    'gateway_order_id' => $bankOrderId,
                    'requested_amount' => $requestedAmount,
                    'gateway_amount'   => $gatewayAmount,
                    'gateway_response' => $response,
                ],
            ]);
        } catch (\Exception $e) {
            Log::channel($alfabankLogChannel)->error('Alfabank customer refund: exception', [
                'customer_id'      => $customer->id,
                'order_id'         => $order->id,
                'gateway_order_id' => $bankOrderId,
                'requested_amount' => $requestedAmount,
                'gateway_amount'   => $gatewayAmount,
                'exception'        => $e->getMessage(),
            ]);

            return response([
                'message' => 'Failed to process refund request.',
            ], 500);
        }
    }

    /**
     * Get detailed reason why order cannot be canceled.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return string
     */
    protected function getCancelErrorReason(Order $order): string
    {
        // Проверяем статус заказа
        if (in_array($order->status, [Order::STATUS_CLOSED, Order::STATUS_FRAUD])) {
            if ($order->status === Order::STATUS_CLOSED) {
                return trans('rest-api::app.shop.sales.orders.error.cancel-reason-closed');
            }

            return trans('rest-api::app.shop.sales.orders.error.cancel-reason-fraud');
        }

        // Проверяем, есть ли позиции, которые уже выставлены в счет
        $hasInvoicedItems = false;
        foreach ($order->items as $item) {
            if ($item->qty_invoiced > 0) {
                $hasInvoicedItems = true;
                break;
            }
        }

        if ($hasInvoicedItems) {
            return trans('rest-api::app.shop.sales.orders.error.cancel-reason-invoiced');
        }

        // Проверяем, все ли позиции уже отменены
        $allItemsCanceled = true;
        foreach ($order->items as $item) {
            if ($item->qty_canceled < $item->qty_ordered) {
                $allItemsCanceled = false;
                break;
            }
        }

        if ($allItemsCanceled) {
            return trans('rest-api::app.shop.sales.orders.error.cancel-reason-already-canceled');
        }

        // Общая причина
        return trans('rest-api::app.shop.sales.orders.error.cancel-reason-general');
    }

    /**
     * Resolve saved Alfabank gateway order id from order data.
     */
    protected function resolveAlfabankOrderId(Order $order): ?string
    {
        $paymentAdditional = $order->payment?->additional ?? [];
        $orderAdditional = $order->additional ?? [];

        $gatewayOrderId = $paymentAdditional['alfabank_order_id']
            ?? $orderAdditional['alfabank_order_id']
            ?? null;

        return is_string($gatewayOrderId) && $gatewayOrderId !== ''
            ? $gatewayOrderId
            : null;
    }

    /**
     * Resolve numeric ISO 4217 code by alpha currency code.
     */
    protected function resolveNumericCurrencyCode(string $currencyCode): ?string
    {
        $codes = [
            'BYN' => '933',
            'BYR' => '974',
            'RUB' => '643',
            'USD' => '840',
            'EUR' => '978',
        ];

        return $codes[strtoupper($currencyCode)] ?? null;
    }

    /**
     * Fetch order status from Alfabank gateway.
     *
     * @throws \RuntimeException
     */
    protected function fetchAlfabankOrderStatus(string $bankOrderId, Order $order): string
    {
        $alfabankLogChannel = config('alfabank-payment.log_channel', 'daily');
        $alfabankApi = app(\Webkul\AlfabankPayment\Services\AlfabankApiService::class);
        $response = $alfabankApi->getOrderStatus($bankOrderId);

        if (isset($response['errorCode']) && (string) $response['errorCode'] !== '0') {
            Log::channel($alfabankLogChannel)->warning('Alfabank customer cancel: status check failed', [
                'order_id'         => $order->id,
                'gateway_order_id' => $bankOrderId,
                'errorCode'        => $response['errorCode'] ?? null,
                'errorMessage'     => $response['errorMessage'] ?? null,
            ]);

            throw new \RuntimeException(
                'Alfabank status check error: ' . ($response['errorMessage'] ?? 'Unknown gateway error')
            );
        }

        if (! array_key_exists('orderStatus', $response)) {
            throw new \RuntimeException('Alfabank status check error: missing orderStatus in gateway response.');
        }

        return (string) $response['orderStatus'];
    }

    /**
     * Check if Alfabank order is paid in gateway.
     */
    protected function isAlfabankOrderPaid(string $gatewayOrderStatus): bool
    {
        return $gatewayOrderStatus === '2';
    }

    /**
     * Send full refund request for Alfabank order.
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    protected function processGatewayFullRefund(Order $order, string $bankOrderId): array
    {
        $alfabankLogChannel = config('alfabank-payment.log_channel', 'daily');
        $currency = $this->resolveNumericCurrencyCode((string) $order->order_currency_code);
        $language = app()->getLocale() ?: 'ru';

        Log::channel($alfabankLogChannel)->info('Alfabank customer cancel: full refund request', [
            'order_id'         => $order->id,
            'gateway_order_id' => $bankOrderId,
        ]);

        $alfabankApi = app(\Webkul\AlfabankPayment\Services\AlfabankApiService::class);
        $response = $alfabankApi->refundOrder($bankOrderId, 0, $language, $currency);

        if (isset($response['errorCode']) && (string) $response['errorCode'] !== '0') {
            Log::channel($alfabankLogChannel)->warning('Alfabank customer cancel: refund failed', [
                'order_id'         => $order->id,
                'gateway_order_id' => $bankOrderId,
                'errorCode'        => $response['errorCode'] ?? null,
                'errorMessage'     => $response['errorMessage'] ?? null,
            ]);

            throw new \RuntimeException(
                'Alfabank refund error: ' . ($response['errorMessage'] ?? 'Unknown gateway error')
            );
        }

        Log::channel($alfabankLogChannel)->info('Alfabank customer cancel: refund accepted', [
            'order_id'         => $order->id,
            'gateway_order_id' => $bankOrderId,
        ]);

        return $response;
    }

    /**
     * Rate customer's order.
     */
    public function rate(Request $request, int $id): \Illuminate\Http\Response
    {
        $order = $this->resolveShopUser($request)->orders()->find($id);

        if (! $order) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.not-found'),
            ], 404);
        }

        $validated = $request->validate([
            'rating' => 'required|boolean',
            'rating_comment' => 'nullable|string|max:1000',
        ]);

        // Конвертируем boolean в правильный формат (true/false)
        $rating = filter_var($validated['rating'], FILTER_VALIDATE_BOOLEAN);

        $order->update([
            'rating' => $rating,
            'rating_comment' => $validated['rating_comment'] ?? null,
        ]);

        return response([
            'data' => new OrderResource($order),
            'message' => trans('rest-api::app.shop.sales.orders.rate-success'),
        ]);
    }

    /**
     * Reorder the specified resource.
     */
    public function reorder(Request $request, int $id): \Illuminate\Http\Response
    {
        $order = $this->resolveShopUser($request)->orders()->findOrFail($id);

        if (
            ! $order->canReorder()
            || ! core()->getConfigData('sales.order_settings.reorder.shop')
        ) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.reorder-error'),
            ], 405);
        }

        foreach ($order->items as $item) {
            try {
                $cart = Cart::addProduct($item->product, $item->additional);
            } catch (\Exception $e) {
                return response([
                    'message' => trans('rest-api::app.shop.sales.orders.error.reorder-error'),
                ], 405);
            }
        }

        return response([
            'data' => new CartResource($cart),
        ]);
    }

    /**
     * Get active orders.
     */
    public function activeOrders(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $channelCode = core()->getCurrentChannelCode();
        $statuses = core()->getConfigData('sales.order_settings.order_statuses.active_statuses', $channelCode);

        if (empty($statuses)) {
            // Default active statuses if not configured
            $statuses = [
                Order::STATUS_PENDING,
                Order::STATUS_PENDING_PAYMENT,
                Order::STATUS_PROCESSING,
                Order::STATUS_PREPARING,
                //Order::STATUS_READY,
            ];
        } else {
            $statuses = is_array($statuses) ? $statuses : explode(',', $statuses);
            $statuses = array_map('trim', $statuses);
        }

        return $this->getOrdersByStatuses($request, $statuses, 'active');
    }

    /**
     * Get completed orders.
     */
    public function completedOrders(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $channelCode = core()->getCurrentChannelCode();
        $statuses = core()->getConfigData('sales.order_settings.order_statuses.completed_statuses', $channelCode);

        if (empty($statuses)) {
            // Default completed statuses if not configured
            $statuses = [
                Order::STATUS_COMPLETED,
                Order::STATUS_CLOSED,
                Order::STATUS_READY,
            ];
        } else {
            $statuses = is_array($statuses) ? $statuses : explode(',', $statuses);
            $statuses = array_map('trim', $statuses);
        }

        return $this->getOrdersByStatuses($request, $statuses, 'completed');
    }

    /**
     * Get cancelled orders.
     */
    public function cancelledOrders(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $channelCode = core()->getCurrentChannelCode();
        $statuses = core()->getConfigData('sales.order_settings.order_statuses.cancelled_statuses', $channelCode);

        if (empty($statuses)) {
            // Default cancelled statuses if not configured
            $statuses = [
                Order::STATUS_CANCELED,
                Order::STATUS_FRAUD,
            ];
        } else {
            $statuses = is_array($statuses) ? $statuses : explode(',', $statuses);
            $statuses = array_map('trim', $statuses);
        }

        return $this->getOrdersByStatuses($request, $statuses, 'cancelled');
    }

    /**
     * Get orders by statuses.
     */
    protected function getOrdersByStatuses(Request $request, array $statuses, string $listType = 'all'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $customerId = $this->resolveShopUser($request)->id;

        $params = array_merge(
            $request->only(['page', 'limit', 'pagination', 'sort', 'order']),
            $request->except(array_merge($this->requestException, ['token'])),
            ['list_type' => $listType, 'statuses' => $statuses]
        );

        $cacheKey = CustomerOrdersCache::key($customerId, $params);

        $data = Cache::remember($cacheKey, CustomerOrdersCache::ttl(), function () use ($request, $statuses) {
            $query = $this->getRepositoryInstance()->scopeQuery(function ($query) use ($request, $statuses) {
                $query = $query->where('customer_id', $this->resolveShopUser($request)->id)
                    ->whereIn('status', $statuses);

                foreach ($request->except(['page', 'limit', 'pagination', 'sort', 'order', 'token']) as $input => $value) {
                    $query = $query->whereIn($input, array_map('trim', explode(',', $value)));
                }

                if ($sort = $request->input('sort')) {
                    $query = $query->orderBy($sort, $request->input('order') ?? 'desc');
                } else {
                    $query = $query->orderBy('id', 'desc');
                }

                return $query;
            });

            $query->with([
                'items.order',
                'items.product.images',
                'items.product.parent.images',
            ]);

            $usePagination = is_null($request->input('pagination')) || $request->input('pagination');

            if ($usePagination) {
                $paginator = $query->paginate($request->input('limit') ?? 10);

                $resourceClass = $this->listResource();

                return [
                    'data'  => $resourceClass::collection($paginator->items())->resolve($request),
                    'links' => [
                        'first' => $paginator->url(1),
                        'last'  => $paginator->url($paginator->lastPage()),
                        'prev'  => $paginator->previousPageUrl(),
                        'next'  => $paginator->nextPageUrl(),
                    ],
                    'meta'  => [
                        'current_page' => $paginator->currentPage(),
                        'from'         => $paginator->firstItem(),
                        'last_page'    => $paginator->lastPage(),
                        'links'        => $paginator->linkCollection()->toArray(),
                        'path'         => $paginator->path(),
                        'per_page'     => $paginator->perPage(),
                        'to'           => $paginator->lastItem(),
                        'total'        => $paginator->total(),
                    ],
                ];
            }

            $results = $query->get();

            $resourceClass = $this->listResource();

            return [
                'data' => $resourceClass::collection($results)->resolve($request),
            ];
        });

        $jsonResponse = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return api_stream_json($jsonResponse, 'orders.json');
    }

    /**
     * Save (or replace) an Apple Live Activity push token for a customer order.
     */
    public function storeLiveActivityToken(Request $request): \Illuminate\Http\Response
    {
        $validated = $request->validate([
            'push_token'   => ['required', 'string', 'max:512'],
            'order_number' => ['required', 'string', 'max:64'],
        ]);

        $customer = $this->resolveShopUser($request);

        $order = $customer->orders()
            ->where('increment_id', $validated['order_number'])
            ->first();

        if (! $order) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.not-found'),
            ], 404);
        }

        OrderLiveActivityToken::updateOrCreate(
            ['order_id' => $order->id],
            [
                'customer_id'        => $customer->id,
                'order_increment_id' => $order->increment_id,
                'push_token'         => $validated['push_token'],
            ]
        );

        return response([
            'message' => trans('rest-api::app.shop.sales.orders.live-activity-token-saved'),
        ]);
    }

    /**
     * Returns an individual resource with eager loading to avoid N+1.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getResource(Request $request, $id)
    {
        $resourceClassName = $this->resource();

        $query = $this->getRepositoryInstance()
            ->with([
                'payment',
                'addresses',
                'items.product.images',
                'items.product.parent.images',
                'items.children',
                'invoices.address',
                'invoices.items',
                'shipments.items',
                'shipments.customer',
                'shipments.inventory_source',
            ]);

        $resource = $this->isAuthorized()
            ? $query->where('customer_id', $this->resolveShopUser($request)->id)->findOrFail($id)
            : $query->findOrFail($id);

        return new $resourceClassName($resource);
    }
}
