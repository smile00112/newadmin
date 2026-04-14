<?php

namespace Webkul\Admin\Http\Controllers\Sales;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\DataGrids\Sales\OrderDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Resources\AddressResource;
use Webkul\Admin\Http\Resources\CartResource;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderStatus;
use Webkul\Sales\Repositories\OrderCommentRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderCommentRepository $orderCommentRepository,
        protected CartRepository $cartRepository,
        protected CustomerGroupRepository $customerGroupRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(OrderDataGrid::class)->process();
        }

        $groups = $this->customerGroupRepository->findWhere([['code', '<>', 'guest']]);

        // Get order statistics by status
        $orderStats = $this->orderRepository
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusFilters = [];
        try {
            $allStatuses = OrderStatus::orderBy('sort_order')->get();
            $statusFilters = $allStatuses->map(function ($s) use ($orderStats) {
                return [
                    'key'   => $s->code,
                    'label' => $s->name,
                    'count' => $orderStats[$s->code] ?? 0,
                    'icon'  => $s->icon ?? 'hourglass-top',
                    'color' => $s->color ?? '#6b7280',
                ];
            })->toArray();
        } catch (\Exception $e) {
            // Fallback if order_statuses table doesn't exist yet
            $statusFilters = [
                ['key' => Order::STATUS_PENDING, 'label' => 'Новый', 'count' => $orderStats[Order::STATUS_PENDING] ?? 0, 'icon' => 'icon-time', 'color' => '#f59e0b'],
                ['key' => Order::STATUS_PROCESSING, 'label' => 'В обработке', 'count' => $orderStats[Order::STATUS_PROCESSING] ?? 0, 'icon' => 'icon-processing', 'color' => '#3b82f6'],
                ['key' => Order::STATUS_COMPLETED, 'label' => 'Выполнен', 'count' => $orderStats[Order::STATUS_COMPLETED] ?? 0, 'icon' => 'icon-done', 'color' => '#22c55e'],
                ['key' => Order::STATUS_CANCELED, 'label' => 'Отменён', 'count' => $orderStats[Order::STATUS_CANCELED] ?? 0, 'icon' => 'icon-cancel', 'color' => '#ef4444'],
            ];
        }

        return view('admin::sales.orders.index', compact('groups', 'statusFilters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(int $cartId)
    {
        $cart = $this->cartRepository->find($cartId);

        if (! $cart) {
            return redirect()->route('admin.sales.orders.index');
        }

        $addresses = AddressResource::collection($cart->customer->addresses);

        $cart = new CartResource($cart);

        return view('admin::sales.orders.create', compact('cart', 'addresses'));
    }

    /**
     * Store order
     */
    public function store(int $cartId)
    {
        $cart = $this->cartRepository->findOrFail($cartId);

        Cart::setCart($cart);

        if (Cart::hasError()) {
            return response()->json([
                'message' => trans('admin::app.sales.orders.create.error'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        Cart::collectTotals();

        try {
            $this->validateOrder();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $cart = Cart::getCart();

        if (! in_array($cart->payment->method, ['cashondelivery', 'moneytransfer'])) {
            return response()->json([
                'message' => trans('admin::app.sales.orders.create.payment-not-supported'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = (new OrderResource($cart))->jsonSerialize();
        $data = OrderResource::mergeCartBonusIntoOrderData($data, $cart);

        $order = $this->orderRepository->create($data);

        Cart::removeCart($cart);

        session()->flash('order', trans('admin::app.sales.orders.create.order-placed-success'));

        return new JsonResource([
            'redirect'     => true,
            'redirect_url' => route('admin.sales.orders.view', $order->id),
        ]);
    }

    /**
     * Show the view for the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function view(int $id)
    {
        $order = $this->orderRepository->findOrFail($id);

        // Customer metrics
        $customerMetrics = null;
        if ($order->customer_id) {
            $customer = $order->customer;
            $customerOrders = $this->orderRepository
                ->where('customer_id', $order->customer_id)
                ->get();

            $totalSpent = $customerOrders->sum('base_grand_total');
            $orderCount = $customerOrders->count();
            $averageCheck = $orderCount > 0 ? $totalSpent / $orderCount : 0;
            $lastOrderDate = $customerOrders->max('created_at');

            $customerMetrics = [
                'order_count'   => $orderCount,
                'total_spent'   => core()->formatBasePrice($totalSpent),
                'average_check' => core()->formatBasePrice($averageCheck),
                'last_order'    => $lastOrderDate ? \Carbon\Carbon::parse($lastOrderDate)->format('d.m.Y') : '-',
                'registered_at' => $customer?->created_at ? $customer->created_at->format('d.m.Y') : '-',
                'phone'         => $order->shipping_address?->phone ?? $order->billing_address?->phone ?? null,
            ];
        }

        // Available payment methods from config
        $paymentMethods = [];
        try {
            $allPaymentConfigs = config('payment_methods', []);
            foreach ($allPaymentConfigs as $code => $config) {
                if (! empty($config['active'])) {
                    $title = core()->getConfigData('sales.payment_methods.' . $code . '.title')
                        ?? ($config['title'] ?? $code);
                    $paymentMethods[] = [
                        'code'  => $code,
                        'title' => $title,
                    ];
                }
            }
        } catch (\Exception $e) {
            // fallback
        }

        // All order statuses for dynamic rendering
        $allStatuses = [];
        $statusColorMap = [];
        try {
            $allStatuses = OrderStatus::allForJs();
            $statusColorMap = OrderStatus::colorMap();
        } catch (\Exception $e) {
            // Fallback if table doesn't exist
            $statusColorMap = [
                'pending' => '#f59e0b', 'pending_payment' => '#f59e0b',
                'processing' => '#3b82f6', 'preparing' => '#6366f1',
                'ready' => '#10b981', 'completed' => '#22c55e',
                'canceled' => '#ef4444', 'closed' => '#6b7280',
            ];
        }

        return view('admin::sales.orders.view', compact('order', 'customerMetrics', 'paymentMethods', 'allStatuses', 'statusColorMap'));
    }

    /**
     * Quick view JSON for modal preview.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickView(int $id)
    {
        $order = $this->orderRepository->with(['items', 'items.product', 'payment', 'addresses'])->findOrFail($id);

        $items = $order->items->map(function ($item) {
            $imageUrl = null;

            if ($item->product?->category_image) {
                $imageUrl = \Illuminate\Support\Facades\Storage::url($item->product->category_image);
            } elseif ($item->product?->base_image_url) {
                $imageUrl = $item->product->base_image_url;
            }

            return [
                'id'            => $item->id,
                'name'          => $item->name,
                'sku'           => $item->sku,
                'qty'           => $item->qty_ordered,
                'price'         => core()->formatBasePrice($item->base_price),
                'total'         => core()->formatBasePrice($item->base_total + $item->base_tax_amount - $item->base_discount_amount),
                'image_url'     => $imageUrl,
            ];
        });

        $shippingAddress = $order->shipping_address;
        $billingAddress  = $order->billing_address;

        $paymentTitle = '';
        if ($order->payment) {
            $paymentTitle = core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') ?? $order->payment->method;
        }

        return response()->json([
            'id'               => $order->id,
            'increment_id'     => $order->increment_id,
            'status'           => $order->status,
            'status_label'     => OrderStatus::nameByCode($order->status),
            'created_at'       => $order->created_at->format('d.m.Y H:i'),
            'customer_name'    => $order->customer_first_name . ' ' . $order->customer_last_name,
            'customer_email'   => $order->customer_email,
            'payment_method'   => $paymentTitle,
            'sub_total'        => core()->formatBasePrice($order->base_sub_total),
            'tax_amount'       => core()->formatBasePrice($order->base_tax_amount),
            'discount'         => core()->formatBasePrice($order->base_discount_amount),
            'grand_total'      => core()->formatBasePrice($order->base_grand_total),
            'total_paid'       => core()->formatBasePrice($order->base_grand_total_invoiced),
            'total_due'        => core()->formatBasePrice($order->status !== 'canceled' ? $order->base_total_due : 0),
            'items'            => $items,
            'shipping_address' => $shippingAddress ? [
                'name'    => $shippingAddress->name,
                'address' => implode(', ', array_filter([$shippingAddress->address1, $shippingAddress->city, $shippingAddress->state, $shippingAddress->postcode, $shippingAddress->country])),
                'phone'   => $shippingAddress->phone,
            ] : null,
            'billing_address'  => $billingAddress ? [
                'name'    => $billingAddress->name,
                'address' => implode(', ', array_filter([$billingAddress->address1, $billingAddress->city, $billingAddress->state, $billingAddress->postcode, $billingAddress->country])),
                'phone'   => $billingAddress->phone,
            ] : null,
        ]);
    }

    /**
     * View panel (partial HTML) for slide-out drawer.
     *
     * @return \Illuminate\View\View
     */
    public function viewPanel(int $id)
    {
        $order = $this->orderRepository->findOrFail($id);

        // Customer metrics
        $customerMetrics = null;
        if ($order->customer_id) {
            $customer = $order->customer;
            $customerOrders = $this->orderRepository
                ->where('customer_id', $order->customer_id)
                ->get();

            $totalSpent = $customerOrders->sum('base_grand_total');
            $orderCount = $customerOrders->count();
            $averageCheck = $orderCount > 0 ? $totalSpent / $orderCount : 0;
            $lastOrderDate = $customerOrders->max('created_at');

            $customerMetrics = [
                'order_count'   => $orderCount,
                'total_spent'   => core()->formatBasePrice($totalSpent),
                'average_check' => core()->formatBasePrice($averageCheck),
                'last_order'    => $lastOrderDate ? \Carbon\Carbon::parse($lastOrderDate)->format('d.m.Y') : '-',
                'registered_at' => $customer?->created_at ? $customer->created_at->format('d.m.Y') : '-',
                'phone'         => $order->shipping_address?->phone ?? $order->billing_address?->phone ?? null,
            ];
        }

        // Available payment methods from config
        $paymentMethods = [];
        try {
            $allPaymentConfigs = config('payment_methods', []);
            foreach ($allPaymentConfigs as $code => $config) {
                if (! empty($config['active'])) {
                    $title = core()->getConfigData('sales.payment_methods.' . $code . '.title')
                        ?? ($config['title'] ?? $code);
                    $paymentMethods[] = [
                        'code'  => $code,
                        'title' => $title,
                    ];
                }
            }
        } catch (\Exception $e) {
            // fallback
        }

        // All order statuses for dynamic rendering
        $allStatuses = [];
        $statusColorMap = [];
        try {
            $allStatuses = OrderStatus::allForJs();
            $statusColorMap = OrderStatus::colorMap();
        } catch (\Exception $e) {
            $statusColorMap = [
                'pending' => '#f59e0b', 'pending_payment' => '#f59e0b',
                'processing' => '#3b82f6', 'preparing' => '#6366f1',
                'ready' => '#10b981', 'completed' => '#22c55e',
                'canceled' => '#ef4444', 'closed' => '#6b7280',
            ];
        }

        return view('admin::sales.orders.view-panel', compact('order', 'customerMetrics', 'paymentMethods', 'allStatuses', 'statusColorMap'));
    }

    /**
     * Reorder action for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function reorder(int $id)
    {
        $order = $this->orderRepository->findOrFail($id);

        $cart = Cart::createCart([
            'customer'  => $order->customer,
            'is_active' => false,
        ]);

        Cart::setCart($cart);

        foreach ($order->items as $item) {
            try {
                Cart::addProduct($item->product, $item->additional);
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return redirect()->route('admin.sales.orders.create', $cart->id);
    }

    /**
     * Cancel action for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(int $id)
    {
        $result = $this->orderRepository->cancel($id);

        if ($result) {
            session()->flash('success', trans('admin::app.sales.orders.view.cancel-success'));
        } else {
            session()->flash('error', trans('admin::app.sales.orders.view.create-error'));
        }

        return redirect()->route('admin.sales.orders.view', $id);
    }

    /**
     * Add comment to the order
     *
     * @return \Illuminate\Http\Response
     */
    public function comment(int $id)
    {
        $validatedData = $this->validate(request(), [
            'comment'           => 'required',
            'customer_notified' => 'sometimes|sometimes',
        ]);

        $validatedData['order_id'] = $id;

        Event::dispatch('sales.order.comment.create.before');

        $comment = $this->orderCommentRepository->create($validatedData);

        Event::dispatch('sales.order.comment.create.after', $comment);

        session()->flash('success', trans('admin::app.sales.orders.view.comment-success'));

        return redirect()->route('admin.sales.orders.view', $id);
    }

    /**
     * Update order status
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(int $id)
    {
        $order = $this->orderRepository->findOrFail($id);

        $validatedData = $this->validate(request(), [
            'status' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Dynamic validation: status must exist in order_statuses table
                    try {
                        $validStatuses = OrderStatus::pluck('code')->toArray();
                    } catch (\Exception $e) {
                        // Fallback if table doesn't exist yet
                        $validStatuses = [
                            Order::STATUS_PENDING,
                            Order::STATUS_PENDING_PAYMENT,
                            Order::STATUS_PROCESSING,
                            Order::STATUS_PREPARING,
                            Order::STATUS_READY,
                            Order::STATUS_COMPLETED,
                            Order::STATUS_CANCELED,
                            Order::STATUS_CLOSED,
                            Order::STATUS_FRAUD,
                        ];
                    }

                    if (! in_array($value, $validStatuses)) {
                        $fail('The selected status is invalid.');
                    }
                },
            ],
        ]);

        $newStatus = $validatedData['status'];

        $isAjax = request()->ajax() || request()->expectsJson();

        // Проверяем, изменился ли статус
        if ($order->status === $newStatus) {
            if ($isAjax) {
                return response()->json(['message' => 'Статус не изменён'], 200);
            }

            session()->flash('info', trans('admin::app.sales.orders.view.status-not-changed'));

            return redirect()->route('admin.sales.orders.view', $id);
        }

        try {
            $this->orderRepository->updateOrderStatus($order, $newStatus);

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Статус заказа обновлён',
                    'status'  => $newStatus,
                    'status_label' => OrderStatus::nameByCode($newStatus),
                ]);
            }

            session()->flash('success', trans('admin::app.sales.orders.view.status-updated-success'));
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json(['message' => 'Ошибка при обновлении статуса: ' . $e->getMessage()], 500);
            }

            session()->flash('error', trans('admin::app.sales.orders.view.status-update-error'));
        }

        return redirect()->route('admin.sales.orders.view', $id);
    }

    /**
     * Bind table number to the order.
     *
     * @return \Illuminate\Http\Response
     */
    public function bindTable(int $id)
    {
        $validatedData = $this->validate(request(), [
            'table_number' => ['required', 'integer', 'min:1'],
        ]);

        $order = $this->orderRepository->findOrFail($id);
        $order->update(['table_number' => $validatedData['table_number']]);

        session()->flash('success', trans('admin::app.sales.orders.view.bind-table-success'));

        return redirect()->route('admin.sales.orders.view', $id);
    }

    /**
     * Unbind table number from the order.
     *
     * @return \Illuminate\Http\Response
     */
    public function unbindTable(int $id)
    {
        $order = $this->orderRepository->findOrFail($id);
        $order->update(['table_number' => null]);

        session()->flash('success', trans('admin::app.sales.orders.view.unbind-table-success'));

        return redirect()->route('admin.sales.orders.view', $id);
    }

    /**
     * Update payment method for an order (SPA).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePayment(int $id)
    {
        $order = $this->orderRepository->findOrFail($id);

        $validatedData = $this->validate(request(), [
            'method' => 'required|string',
        ]);

        try {
            $order->payment->update(['method' => $validatedData['method']]);

            $methodLabel = core()->getConfigData('sales.payment_methods.' . $validatedData['method'] . '.title') ?? $validatedData['method'];

            return response()->json([
                'success'      => true,
                'message'      => 'Способ оплаты обновлён',
                'method_label' => $methodLabel,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update order item quantities (SPA).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItems(int $id)
    {
        $order = $this->orderRepository->findOrFail($id);

        $validatedData = $this->validate(request(), [
            'items'       => 'required|array|min:1',
            'items.*.id'  => 'required|integer',
            'items.*.qty' => 'required|integer|min:0',
        ]);

        try {
            $newSubTotal = 0;
            $newTaxTotal = 0;
            $newDiscountTotal = 0;
            $updatedItems = [];

            foreach ($validatedData['items'] as $itemData) {
                $item = $order->items()->findOrFail($itemData['id']);
                $oldQty = $item->qty_ordered;
                $newQty = $itemData['qty'];

                if ($newQty !== $oldQty) {
                    $pricePerUnit = $item->base_price;
                    $taxPerUnit = $oldQty > 0 ? $item->base_tax_amount / $oldQty : 0;
                    $discountPerUnit = $oldQty > 0 ? $item->base_discount_amount / $oldQty : 0;

                    $item->update([
                        'qty_ordered'          => $newQty,
                        'base_total'           => $pricePerUnit * $newQty,
                        'total'                => $pricePerUnit * $newQty,
                        'base_tax_amount'      => $taxPerUnit * $newQty,
                        'tax_amount'           => $taxPerUnit * $newQty,
                        'base_discount_amount' => $discountPerUnit * $newQty,
                        'discount_amount'      => $discountPerUnit * $newQty,
                        'base_total_incl_tax'  => ($pricePerUnit + $taxPerUnit) * $newQty,
                        'total_incl_tax'       => ($pricePerUnit + $taxPerUnit) * $newQty,
                    ]);
                }

                $item->refresh();
                $newSubTotal += $item->base_total;
                $newTaxTotal += $item->base_tax_amount;
                $newDiscountTotal += $item->base_discount_amount;

                $imageUrl = null;
                if ($item->product?->category_image) {
                    $imageUrl = \Illuminate\Support\Facades\Storage::url($item->product->category_image);
                } elseif ($item->product?->base_image_url) {
                    $imageUrl = $item->product->base_image_url;
                }

                $updatedItems[] = [
                    'id'                  => $item->id,
                    'name'                => $item->name,
                    'sku'                 => $item->sku,
                    'qty_ordered'         => $item->qty_ordered,
                    'base_price'          => $item->base_price,
                    'base_total'          => $item->base_total,
                    'base_tax_amount'     => $item->base_tax_amount,
                    'base_discount_amount'=> $item->base_discount_amount,
                    'formatted_price'     => core()->formatBasePrice($item->base_price),
                    'image_url'           => $imageUrl,
                ];
            }

            // Recalculate order totals
            $newGrandTotal = $newSubTotal + $newTaxTotal - $newDiscountTotal + $order->base_shipping_amount;
            $order->update([
                'base_sub_total'     => $newSubTotal,
                'sub_total'          => $newSubTotal,
                'base_tax_amount'    => $newTaxTotal,
                'tax_amount'         => $newTaxTotal,
                'base_discount_amount' => $newDiscountTotal,
                'discount_amount'    => $newDiscountTotal,
                'base_grand_total'   => $newGrandTotal,
                'grand_total'        => $newGrandTotal,
                'base_total_due'     => $newGrandTotal - $order->base_grand_total_invoiced,
            ]);

            $order->refresh();

            return response()->json([
                'success'     => true,
                'message'     => 'Товары обновлены',
                'items'       => $updatedItems,
                'totals'      => [
                    'sub_total'          => core()->formatBasePrice($order->base_sub_total),
                    'sub_total_incl_tax' => core()->formatBasePrice($order->base_sub_total + $order->base_tax_amount),
                    'tax'                => core()->formatBasePrice($order->base_tax_amount),
                    'discount'           => core()->formatBasePrice($order->base_discount_amount),
                    'grand_total'        => core()->formatBasePrice($order->base_grand_total),
                    'total_paid'         => core()->formatBasePrice($order->base_grand_total_invoiced),
                    'total_refunded'     => core()->formatBasePrice($order->base_grand_total_refunded),
                    'total_due'          => core()->formatBasePrice($order->base_total_due),
                ],
                'grand_total' => core()->formatBasePrice($newGrandTotal),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Result of search product.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        $orders = $this->orderRepository->scopeQuery(function ($query) {
            return $query->where('customer_email', 'like', '%'.urldecode(request()->input('query')).'%')
                ->orWhere('status', 'like', '%'.urldecode(request()->input('query')).'%')
                ->orWhere(DB::raw('CONCAT('.DB::getTablePrefix().'customer_first_name, " ", '.DB::getTablePrefix().'customer_last_name)'), 'like', '%'.urldecode(request()->input('query')).'%')
                ->orWhere('increment_id', request()->input('query'))
                ->orderBy('created_at', 'desc');
        })->paginate(10);

        foreach ($orders as $key => $order) {
            $orders[$key]['formatted_created_at'] = core()->formatDate($order->created_at, 'd M Y');

            $orders[$key]['status_label'] = $order->status_label;

            $orders[$key]['customer_full_name'] = $order->customer_full_name;
        }

        return response()->json($orders);
    }

    /**
     * Validate order before creation.
     *
     * @return void|\Exception
     */
    public function validateOrder()
    {
        $cart = Cart::getCart();

        if (! Cart::haveMinimumOrderAmount()) {
            throw new \Exception(trans('admin::app.sales.orders.create.minimum-order-error', [
                'amount' => core()->formatPrice(core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0),
            ]));
        }

        // Проверяем, выбран ли самовывоз или еда в зале
        $skipAddressValidation = in_array($cart->shipping_method, ['pickup_pickup', 'dinein_dinein']);

        if (
            $cart->haveStockableItems()
            && ! $skipAddressValidation
            && ! $cart->shipping_address
        ) {
            throw new \Exception(trans('admin::app.sales.orders.create.check-shipping-address'));
        }

        if (! $skipAddressValidation && ! $cart->billing_address) {
            throw new \Exception(trans('admin::app.sales.orders.create.check-billing-address'));
        }

        if (
            $cart->haveStockableItems()
            && ! $cart->selected_shipping_rate
        ) {
            throw new \Exception(trans('admin::app.sales.orders.create.specify-shipping-method'));
        }

        if (! $cart->payment) {
            throw new \Exception(trans('admin::app.sales.orders.create.specify-payment-method'));
        }
    }
    
    /**
     * Mass update order statuses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function massUpdateStatus()
    {
        $validatedData = $this->validate(request(), [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|integer|exists:orders,id',
            'status' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Dynamic validation: status must exist in order_statuses table
                    try {
                        $validStatuses = OrderStatus::pluck('code')->toArray();
                    } catch (\Exception $e) {
                        // Fallback if table doesn't exist yet
                        $validStatuses = [
                            Order::STATUS_PENDING,
                            Order::STATUS_PENDING_PAYMENT,
                            Order::STATUS_PROCESSING,
                            Order::STATUS_PREPARING,
                            Order::STATUS_READY,
                            Order::STATUS_COMPLETED,
                            Order::STATUS_CANCELED,
                            Order::STATUS_CLOSED,
                            Order::STATUS_FRAUD,
                        ];
                    }

                    if (! in_array($value, $validStatuses)) {
                        $fail('The selected status is invalid.');
                    }
                },
            ],
        ]);

        $orderIds = $validatedData['order_ids'];
        $newStatus = $validatedData['status'];
        $successCount = 0;
        $errorCount = 0;

        foreach ($orderIds as $orderId) {
            try {
                $order = $this->orderRepository->findOrFail($orderId);
                
                if ($order->status !== $newStatus) {
                    $this->orderRepository->updateOrderStatus($order, $newStatus);
                    $successCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Статус обновлен для {$successCount} заказов" . ($errorCount > 0 ? ", ошибок: {$errorCount}" : ''),
            'updated_count' => $successCount,
            'error_count' => $errorCount,
        ]);
    }

    /**
     * Get pending orders for notifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingOrders()
    {
        return Cache::remember('pending_orders_notification', 15, function () {
            $pendingStatuses = [
                Order::STATUS_PENDING,
                Order::STATUS_PENDING_PAYMENT,
            ];

            $orders = Order::select([
                    'id',
                    'increment_id',
                    'status',
                    'customer_first_name',
                    'customer_last_name',
                    'customer_email',
                    'grand_total',
                    'created_at',
                ])
                ->withCount('items')
                ->whereIn('status', $pendingStatuses)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id'                    => $order->id,
                    'increment_id'          => $order->increment_id,
                    'status'                => $order->status,
                    'customer_name'         => $order->customer_first_name . ' ' . $order->customer_last_name,
                    'customer_email'        => $order->customer_email,
                    'grand_total'           => $order->grand_total,
                    'formatted_grand_total' => core()->formatPrice($order->grand_total),
                    'items_count'           => $order->items_count,
                    'created_at'            => $order->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'count'   => $formattedOrders->count(),
                'orders'  => $formattedOrders,
            ]);
        });
    }
}
