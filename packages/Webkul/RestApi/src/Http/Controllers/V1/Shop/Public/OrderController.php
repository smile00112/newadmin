<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Public;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\RestApi\Http\Resources\V1\Shop\Sales\OrderListResource;
use Webkul\Sales\Models\Order;

class OrderController extends Controller
{
    /**
     * Return paginated list of all orders for all customers (public endpoint).
     */
    public function index(Request $request)
    {
        $query = Order::query();

        foreach ($request->except(['page', 'limit', 'pagination', 'sort', 'order']) as $input => $value) {
            $query->whereIn($input, array_map('trim', explode(',', $value)));
        }

        if ($sort = $request->input('sort')) {
            $query->orderBy($sort, $request->input('order') ?? 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $usePagination = is_null($request->input('pagination')) || $request->input('pagination');

        if (! $usePagination) {
            $results = $query->get();

            return [
                'data' => OrderListResource::collection($results)->resolve($request),
            ];
        }

        $limit = (int) $request->input('limit', 10);
        $limit = max(1, min($limit, 50));

        $paginator = $query->paginate($limit);

        $resourceClass = OrderListResource::class;

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
}

