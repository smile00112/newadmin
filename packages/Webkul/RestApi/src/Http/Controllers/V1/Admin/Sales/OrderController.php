<?php

namespace Webkul\RestApi\Http\Controllers\V1\Admin\Sales;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Webkul\RestApi\Http\Resources\V1\Admin\Sales\OrderResource;
use Webkul\Sales\Repositories\OrderCommentRepository;
use Webkul\Sales\Repositories\OrderRepository;

class OrderController extends SalesController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected OrderCommentRepository $orderCommentRepository) {}

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
     * Cancel action for the specified resource.
     */
    public function cancel(int $id): Response
    {
        $result = $this->getRepositoryInstance()->cancel($id);

        return $result
            ? response(['message' => trans('rest-api::app.admin.sales.orders.cancel-success')])
            : response(['message' => trans('rest-api::app.admin.sales.orders.error.cancel-error')], 500);
    }

    /**
     * Add comment to the order.
     */
    public function comment(Request $request, int $id): Response
    {
        $validatedData = $request->validate([
            'comment'           => 'required',
            'customer_notified' => 'sometimes',
        ]);

        $data = array_merge($validatedData, ['order_id' => $id]);

        Event::dispatch('sales.order.comment.create.before', $data);

        $comment = $this->orderCommentRepository->create($data);

        Event::dispatch('sales.order.comment.create.after', $comment);

        return response([
            'data'    => $comment,
            'message' => trans('rest-api::app.admin.sales.orders.comments.create-success'),
        ]);
    }

    /**
     * Bind table number to the order.
     */
    public function bindTable(Request $request): Response
    {
        $validatedData = $request->validate([
            'order_id'     => ['required', 'integer', 'exists:orders,id'],
            'table_number' => ['required', 'integer', 'min:1'],
        ]);

        $order = $this->getRepositoryInstance()->findOrFail($validatedData['order_id']);
        $order->update(['table_number' => $validatedData['table_number']]);

        $resourceClassName = $this->resource();

        return response([
            'data'    => new $resourceClassName($order->fresh()),
            'message' => trans('rest-api::app.admin.sales.orders.bind-table-success'),
        ]);
    }

    /**
     * Unbind table number from the order.
     */
    public function unbindTable(Request $request): Response
    {
        $validatedData = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = $this->getRepositoryInstance()->findOrFail($validatedData['order_id']);
        $order->update(['table_number' => null]);

        $resourceClassName = $this->resource();

        return response([
            'data'    => new $resourceClassName($order->fresh()),
            'message' => trans('rest-api::app.admin.sales.orders.unbind-table-success'),
        ]);
    }
}
