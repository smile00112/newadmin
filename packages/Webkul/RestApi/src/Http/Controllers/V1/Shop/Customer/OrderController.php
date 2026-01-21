<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Webkul\Checkout\Facades\Cart;
use Webkul\RestApi\Http\Resources\V1\Shop\Checkout\CartResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Sales\OrderResource;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;

class OrderController extends CustomerController
{
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
        ]);

        // Конвертируем boolean в правильный формат (true/false)
        $rating = filter_var($validated['rating'], FILTER_VALIDATE_BOOLEAN);

        $order->update([
            'rating' => $rating,
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
}
