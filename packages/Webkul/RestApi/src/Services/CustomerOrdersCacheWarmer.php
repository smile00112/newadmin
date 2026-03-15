<?php

namespace Webkul\RestApi\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webkul\RestApi\Http\Resources\V1\Shop\Sales\OrderListResource;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;

class CustomerOrdersCacheWarmer
{
    /**
     * Default query params used for cache warming (first page, default limit).
     */
    protected const DEFAULT_PARAMS = [
        'page'       => 1,
        'limit'      => 10,
        'pagination' => 1,
        'sort'       => 'id',
        'order'      => 'desc',
    ];

    /**
     * Warm cache for active and completed orders for the given customer and channel.
     */
    public function warm(int $customerId, string $channelCode): void
    {
        $listTypes = [
            'active'   => [$this, 'getActiveStatuses'],
            'completed' => [$this, 'getCompletedStatuses'],
        ];

        foreach ($listTypes as $listType => $statusesResolver) {
            try {
                $this->warmListType($customerId, $channelCode, $listType, $statusesResolver($channelCode));
            } catch (\Throwable $e) {
                Log::warning('RestApi: failed to warm customer orders cache', [
                    'customer_id'   => $customerId,
                    'channel_code'  => $channelCode,
                    'list_type'     => $listType,
                    'message'       => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Warm cache for one list type (active or completed).
     */
    protected function warmListType(int $customerId, string $channelCode, string $listType, array $statuses): void
    {
        $params = array_merge(
            self::DEFAULT_PARAMS,
            ['list_type' => $listType, 'statuses' => $statuses]
        );

        $cacheKey = CustomerOrdersCache::key($customerId, $params);

        $request = Request::create('/api/v1/customer/'.$listType.'-orders', 'GET', $params);

        $data = $this->fetchOrdersData($customerId, $statuses, $request);

        Cache::put($cacheKey, $data, CustomerOrdersCache::ttl());
    }

    /**
     * Fetch orders and build response array (same structure as OrderController::getOrdersByStatuses).
     */
    protected function fetchOrdersData(int $customerId, array $statuses, Request $request): array
    {
        $repository = app(OrderRepository::class);

        $query = $repository->scopeQuery(function ($query) use ($customerId, $statuses, $request) {
            $query = $query->where('customer_id', $customerId)
                ->whereIn('status', $statuses);

            $sort = $request->input('sort', 'id');
            $order = $request->input('order', 'desc');

            return $query->orderBy($sort, $order);
        });

        $query->with([
            'items.order',
            'items.product.images',
            'items.product.parent.images',
        ]);

        $limit = (int) ($request->input('limit') ?? 10);
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

    /**
     * Get active order statuses for channel (same logic as OrderController::activeOrders).
     */
    protected function getActiveStatuses(string $channelCode): array
    {
        $statuses = core()->getConfigData('sales.order_settings.order_statuses.active_statuses', $channelCode);

        if (empty($statuses)) {
            return [
                Order::STATUS_PENDING,
                Order::STATUS_PENDING_PAYMENT,
                Order::STATUS_PROCESSING,
                Order::STATUS_PREPARING,
                Order::STATUS_READY,
            ];
        }

        $statuses = is_array($statuses) ? $statuses : explode(',', $statuses);

        return array_map('trim', $statuses);
    }

    /**
     * Get completed order statuses for channel (same logic as OrderController::completedOrders).
     */
    protected function getCompletedStatuses(string $channelCode): array
    {
        $statuses = core()->getConfigData('sales.order_settings.order_statuses.completed_statuses', $channelCode);

        if (empty($statuses)) {
            return [
                Order::STATUS_COMPLETED,
                Order::STATUS_CLOSED,
            ];
        }

        $statuses = is_array($statuses) ? $statuses : explode(',', $statuses);

        return array_map('trim', $statuses);
    }
}
