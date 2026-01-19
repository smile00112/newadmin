<?php

namespace Webkul\IikoIntegration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;
use Webkul\IikoIntegration\Services\IikoMenuService;
use Webkul\IikoIntegration\Services\IikoOrderService;
use Webkul\IikoIntegration\Services\IikoOrganizationService;
use Webkul\Sales\Repositories\OrderRepository;

class IikoSyncController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected IikoOrganizationService $organizationService,
        protected IikoMenuService $menuService,
        protected IikoOrderService $orderService,
        protected OrderRepository $orderRepository,
        protected IikoSyncLogRepository $syncLogRepository
    ) {}

    /**
     * Display sync page.
     */
    public function index(): View
    {
        $recentLogs = $this->syncLogRepository->getRecentErrors(10);
        $organizations = $this->organizationService->getOrganizations();

        return view('iiko-integration::admin.iiko.sync', compact(
            'recentLogs',
            'organizations'
        ));
    }

    /**
     * Sync organizations.
     */
    public function syncOrganizations(): JsonResponse
    {
        try {
            $success = $this->organizationService->syncOrganizations();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => trans('iiko-integration::app.sync.organizations-success'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.sync.organizations-failed'),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.sync.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync menu.
     */
    public function syncMenu(): JsonResponse
    {
        try {
            $organizationId = request('organization_id');

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.sync.organization-id-required'),
                ], 400);
            }

            $success = $this->menuService->syncMenu($organizationId);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => trans('iiko-integration::app.sync.menu-success'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.sync.menu-failed'),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.sync.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync specific order.
     */
    public function syncOrder(int $orderId): JsonResponse
    {
        try {
            $order = $this->orderRepository->findOrFail($orderId);

            $success = $this->orderService->syncOrderToIiko($order);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => trans('iiko-integration::app.sync.order-success'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.sync.order-failed'),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.sync.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View sync logs.
     */
    public function viewSyncLog(): View
    {
        $syncType = request('sync_type');
        $logs = $syncType
            ? $this->syncLogRepository->getBySyncType($syncType, 100)
            : $this->syncLogRepository->model->orderBy('created_at', 'desc')->limit(100)->get();

        return view('iiko-integration::admin.iiko.logs', compact('logs', 'syncType'));
    }
}
