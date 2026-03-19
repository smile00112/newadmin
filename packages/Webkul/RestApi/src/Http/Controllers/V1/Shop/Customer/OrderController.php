<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Checkout\Facades\Cart;
use Webkul\RestApi\Http\Resources\V1\Shop\Checkout\CartResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Sales\OrderListResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Sales\OrderResource;
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

        $gatewayOrderId = $validated['gateway_order_id'];

        try {
            $alfabankApi = app(\Webkul\AlfabankPayment\Services\AlfabankApiService::class);

            $response = $alfabankApi->getOrderStatus($gatewayOrderId);

            if (isset($response['errorCode']) && $response['errorCode'] !== '0') {
                Log::warning('Alfabank confirmPayment: gateway returned error', [
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
            $expectedAmount = (int) round($order->grand_total * 100);

            if ($orderStatus !== 2 || $amount !== $expectedAmount) {
                Log::warning('Alfabank confirmPayment: status or amount mismatch', [
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
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Alfabank confirmPayment: failed to create invoice or update order', [
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
        $order = $this->resolveShopUser($request)->orders()->find($id);

        if (! $order) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.error.not-found'),
            ], 404);
        }

        if ($this->getRepositoryInstance()->cancel($order)) {
            return response([
                'message' => trans('rest-api::app.shop.sales.orders.cancel'),
            ]);
        }

        // Определяем причину, почему заказ не может быть отменен
        $reason = $this->getCancelErrorReason($order);

        return response([
            'message' => trans('rest-api::app.shop.sales.orders.error.cancel-error'),
            'reason' => $reason,
        ], 422);
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
