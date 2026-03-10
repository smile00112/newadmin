<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Sales\Models\OrderStatus;
use Webkul\Sales\Models\OrderWorkflowSetting;

class OrderStatusController extends Controller
{
    /**
     * Display the order status settings page.
     */
    public function index()
    {
        $statuses         = OrderStatus::orderBy('sort_order')->get();
        $workflowSettings = OrderWorkflowSetting::allAsArray();

        // Load real shipping methods from system config
        $systemShippingMethods = [];
        foreach (config('carriers', []) as $code => $cfg) {
            $title = core()->getConfigData('sales.carriers.' . $code . '.title') ?? ($cfg['title'] ?? $code);
            $active = (bool) (core()->getConfigData('sales.carriers.' . $code . '.active') ?? ($cfg['active'] ?? false));
            $systemShippingMethods[] = [
                'code'   => $code,
                'title'  => $title,
                'active' => $active,
            ];
        }

        // Load real payment methods from system config
        $systemPaymentMethods = [];
        foreach (config('payment_methods', []) as $code => $cfg) {
            $title = core()->getConfigData('sales.payment_methods.' . $code . '.title') ?? ($cfg['title'] ?? $code);
            $active = (bool) (core()->getConfigData('sales.payment_methods.' . $code . '.active') ?? ($cfg['active'] ?? false));
            $systemPaymentMethods[] = [
                'code'   => $code,
                'title'  => $title,
                'active' => $active,
            ];
        }

        return view('admin::settings.order-statuses.index', compact(
            'statuses',
            'workflowSettings',
            'systemShippingMethods',
            'systemPaymentMethods'
        ));
    }

    /**
     * Save all settings via AJAX.
     */
    public function save(): JsonResponse
    {
        $data = request()->all();

        // 1. Save statuses
        if (isset($data['statuses']) && is_array($data['statuses'])) {
            $existingCodes = OrderStatus::pluck('code')->toArray();
            $incomingCodes = [];

            foreach ($data['statuses'] as $index => $statusData) {
                $code = $statusData['code'] ?? null;
                if (! $code) continue;

                $incomingCodes[] = $code;

                OrderStatus::updateOrCreate(
                    ['code' => $code],
                    [
                        'name'       => $statusData['name'] ?? $code,
                        'icon'       => $statusData['icon'] ?? null,
                        'color'      => $statusData['color'] ?? null,
                        'sort_order' => $index,
                        'is_system'  => (bool) ($statusData['is_system'] ?? false),
                    ]
                );
            }

            // Delete statuses not in the incoming list (only non-system)
            $toDelete = array_diff($existingCodes, $incomingCodes);
            if ($toDelete) {
                OrderStatus::whereIn('code', $toDelete)
                    ->where('is_system', false)
                    ->delete();
            }

            // Clear status label cache in Order model
            $this->invalidateStatusLabelCache();

            // Clear mobile app settings cache
            $this->invalidateMobileAppCache();
        }

        // 2. Save workflow settings
        foreach (['new_order_status', 'pipelines', 'tab_groups', 'delivery_types', 'payment_types'] as $key) {
            if (isset($data[$key])) {
                OrderWorkflowSetting::set($key, $data[$key]);
            }
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Настройки статусов сохранены.',
        ]);
    }

    /**
     * Invalidate the status label cache in Order model.
     * This must be called after any status changes to ensure fresh data.
     */
    private function invalidateStatusLabelCache(): void
    {
        \Webkul\Sales\Models\Order::invalidateStatusLabelCache();
    }

    /**
     * Invalidate mobile app settings cache when statuses change.
     * This ensures the mobile app gets fresh status data on next request.
     */
    private function invalidateMobileAppCache(): void
    {
        try {
            \Webkul\MobileApp\Http\Controllers\Api\MobileSettingsController::clearCache();
        } catch (\Exception $e) {
            // Silently fail if mobile app package is not available
        }
    }

    /**
     * API: get all statuses (for use in other components).
     */
    public function statuses(): JsonResponse
    {
        return new JsonResponse([
            'statuses' => OrderStatus::orderBy('sort_order')->get(),
        ]);
    }
}
