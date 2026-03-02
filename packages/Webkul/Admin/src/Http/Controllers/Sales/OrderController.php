<?php

namespace Webkul\Admin\Http\Controllers\Sales;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
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

        $statusFilters = [
            [
                'key'   => Order::STATUS_PENDING,
                'label' => 'Новый',
                'count' => $orderStats[Order::STATUS_PENDING] ?? 0,
                'icon'  => 'icon-time',
            ],
            [
                'key'   => Order::STATUS_PENDING_PAYMENT,
                'label' => 'Ожидание оплаты',
                'count' => $orderStats[Order::STATUS_PENDING_PAYMENT] ?? 0,
                'icon'  => 'icon-dollar',
            ],
            [
                'key'   => Order::STATUS_PROCESSING,
                'label' => 'В обработке',
                'count' => $orderStats[Order::STATUS_PROCESSING] ?? 0,
                'icon'  => 'icon-processing',
            ],
            [
                'key'   => Order::STATUS_PREPARING,
                'label' => 'Готовим',
                'count' => $orderStats[Order::STATUS_PREPARING] ?? 0,
                'icon'  => 'icon-product',
            ],
            [
                'key'   => Order::STATUS_READY,
                'label' => 'Готов',
                'count' => $orderStats[Order::STATUS_READY] ?? 0,
                'icon'  => 'icon-checkmark',
            ],
            [
                'key'   => Order::STATUS_COMPLETED,
                'label' => 'Выполнен',
                'count' => $orderStats[Order::STATUS_COMPLETED] ?? 0,
                'icon'  => 'icon-done',
            ],
            [
                'key'   => Order::STATUS_CANCELED,
                'label' => 'Отменён',
                'count' => $orderStats[Order::STATUS_CANCELED] ?? 0,
                'icon'  => 'icon-cancel',
            ],
            [
                'key'   => Order::STATUS_CLOSED,
                'label' => 'Закрыт',
                'count' => $orderStats[Order::STATUS_CLOSED] ?? 0,
                'icon'  => 'icon-cancel',
            ],
        ];

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

        return view('admin::sales.orders.view', compact('order'));
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

                    if (! in_array($value, $validStatuses)) {
                        $fail('The selected status is invalid.');
                    }
                },
            ],
        ]);

        $newStatus = $validatedData['status'];

        // Проверяем, изменился ли статус
        if ($order->status === $newStatus) {
            session()->flash('info', trans('admin::app.sales.orders.view.status-not-changed'));

            return redirect()->route('admin.sales.orders.view', $id);
        }

        try {
            $this->orderRepository->updateOrderStatus($order, $newStatus);

            session()->flash('success', trans('admin::app.sales.orders.view.status-updated-success'));
        } catch (\Exception $e) {
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
        $pendingStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PENDING_PAYMENT,
        ];
        
        $orders = $this->orderRepository
            ->whereIn('status', $pendingStatuses)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'increment_id' => $order->increment_id,
                    'status' => $order->status,
                    'customer_name' => $order->customer_first_name . ' ' . $order->customer_last_name,
                    'customer_email' => $order->customer_email,
                    'grand_total' => $order->grand_total,
                    'formatted_grand_total' => core()->formatPrice($order->grand_total),
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->toISOString(),
                ];
            });
        
        return response()->json([
            'success' => true,
            'count' => $orders->count(),
            'orders' => $orders,
        ]);
    }
}
