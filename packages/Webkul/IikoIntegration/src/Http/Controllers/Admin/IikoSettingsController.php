<?php

namespace Webkul\IikoIntegration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\IikoIntegration\Config\IikoFieldsConfig;
use Webkul\IikoIntegration\Repositories\IikoPaymentTypeRepository;
use Webkul\IikoIntegration\Repositories\IikoSettingRepository;
use Webkul\IikoIntegration\Services\IikoAuthService;
use Webkul\IikoIntegration\Services\IikoPaymentTypeService;
use Webkul\Payment\Facades\Payment;

class IikoSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected IikoSettingRepository $settingRepository,
        protected IikoFieldsConfig $fieldsConfig,
        protected IikoAuthService $authService,
        protected IikoPaymentTypeRepository $paymentTypeRepository,
        protected IikoPaymentTypeService $paymentTypeService
    ) {}

    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $channelCode = request('channel', core()->getDefaultChannelCode());
        $activeTab = request('tab', 'configuration');
        $allFields = $this->settingRepository->getSettingsWithFields($channelCode);

        // Group fields by tab
        $fieldsByTab = [];
        foreach ($allFields as $field) {
            $group = $field['group'] ?? 'configuration';
            if (!isset($fieldsByTab[$group])) {
                $fieldsByTab[$group] = [];
            }
            $fieldsByTab[$group][] = $field;
        }

        // Define tabs
        $tabs = [
            'configuration' => trans('iiko-integration::app.settings.configuration'),
            'import' => trans('iiko-integration::app.settings.import'),
            'payment_methods' => trans('iiko-integration::app.settings.payment-methods'),
        ];

        // Get fields for active tab
        $fields = $fieldsByTab[$activeTab] ?? [];

        return view('iiko-integration::admin.iiko.settings', compact(
            'fields',
            'channelCode',
            'tabs',
            'activeTab'
        ));
    }

    /**
     * Store settings.
     */
    public function store(): RedirectResponse
    {
        $channelCode = request('channel_code');
        $settings = request('settings', []);

        $this->settingRepository->saveSettings(
            \Webkul\IikoIntegration\Models\IikoSetting::CHANNEL,
            $settings,
            $channelCode
        );

        // Clear cache
        $this->settingRepository->clearCache(
            \Webkul\IikoIntegration\Models\IikoSetting::CHANNEL,
            $channelCode
        );
        $this->authService->clearTokenCache($channelCode);

        session()->flash('success', trans('iiko-integration::app.settings.save-success'));

        return redirect()->back();
    }

    /**
     * Test connection to iiko API.
     */
    public function testConnection(): JsonResponse
    {
        try {
            $token = $this->authService->getAccessToken();

            if ($token) {
                return response()->json([
                    'success' => true,
                    'message' => trans('iiko-integration::app.settings.connection-success'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.settings.connection-failed'),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.settings.connection-error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment methods mapping.
     */
    public function getPaymentMethodsMapping(Request $request): JsonResponse
    {
        try {
            $channelCode = $request->input('channel', core()->getDefaultChannelCode());
            $organizationId = $this->settingRepository->getSettingWithFallback('organization_id', $channelCode);

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.settings.organization-id-required'),
                ], 400);
            }

            // Get payment types from iiko
            $iikoPaymentTypes = $this->paymentTypeRepository->getByOrganizationId($organizationId);

            // Get payment methods from admin
            $adminPaymentMethods = Payment::getPaymentMethods();

            // Prepare mapping data
            $mapping = $iikoPaymentTypes->map(function ($iikoPaymentType) use ($adminPaymentMethods) {
                return [
                    'id'                    => $iikoPaymentType->id,
                    'iiko_id'               => $iikoPaymentType->iiko_id,
                    'name'                  => $iikoPaymentType->name,
                    'kind'                  => $iikoPaymentType->kind,
                    'is_active'             => $iikoPaymentType->is_active,
                    'payment_method_code'   => $iikoPaymentType->payment_method_code,
                ];
            })->toArray();

            return response()->json([
                'success'              => true,
                'iiko_payment_types'   => $mapping,
                'admin_payment_methods' => $adminPaymentMethods,
                'organization_id'      => $organizationId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.settings.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store payment methods mapping.
     */
    public function storePaymentMethodsMapping(Request $request): JsonResponse
    {
        try {
            $mappings = $request->input('mappings', []);

            foreach ($mappings as $mapping) {
                $paymentTypeId = $mapping['id'] ?? null;
                $paymentMethodCode = $mapping['payment_method_code'] ?? null;

                if ($paymentTypeId) {
                    $this->paymentTypeRepository->update([
                        'payment_method_code' => $paymentMethodCode ?: null,
                    ], $paymentTypeId);
                }
            }

            return response()->json([
                'success' => true,
                'message' => trans('iiko-integration::app.settings.payment-methods-mapping-saved'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.settings.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync payment types from iiko.
     */
    public function syncPaymentTypes(Request $request): JsonResponse
    {
        try {
            $channelCode = $request->input('channel', core()->getDefaultChannelCode());
            $organizationId = $this->settingRepository->getSettingWithFallback('organization_id', $channelCode);

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.settings.organization-id-required'),
                ], 400);
            }

            $success = $this->paymentTypeService->syncPaymentTypes($organizationId, $channelCode);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => trans('iiko-integration::app.settings.payment-types-synced'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.settings.payment-types-sync-failed'),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.settings.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }
}
