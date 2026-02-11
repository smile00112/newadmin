<?php

namespace Webkul\TochkaPayment\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\TochkaPayment\Services\SettingsService;

class SettingsController extends Controller
{
    /**
     * Settings service instance.
     *
     * @var \Webkul\TochkaPayment\Services\SettingsService
     */
    protected $settingsService;

    /**
     * Create a new controller instance.
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Display the settings form.
     */
    public function index(): View
    {
        $settings = $this->settingsService->getSettingsModel();

        // Create empty model if settings don't exist
        if (!$settings) {
            $settings = new \Webkul\TochkaPayment\Models\TochkaPaymentSettings();
        }

        return view('tochka-payment::admin.settings.index', compact('settings'));
    }

    /**
     * Store or update settings.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|string|max:255',
            'jwt_token' => 'nullable|string',
            'api_base_url' => 'nullable|url|max:255',
            'webhook_url' => 'nullable|url|max:255',
            'customer_code' => 'nullable|string|max:255',
            'merchant_id' => 'nullable|string|max:255',
            'payment_mode' => 'nullable|string',
            'save_card' => 'nullable|boolean',
            'pre_authorization' => 'nullable|boolean',
            'ttl' => 'nullable|integer|min:1|max:43200',
            'min_amount' => 'nullable|numeric|min:0.01',
            'is_active' => 'nullable|boolean',
        ]);

        // Validate settings
        $errors = $this->settingsService->validateSettings($validated);

        if (!empty($errors)) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        // Parse payment_mode if it's a string
        if (isset($validated['payment_mode']) && is_string($validated['payment_mode'])) {
            $validated['payment_mode'] = array_map('trim', explode(',', $validated['payment_mode']));
        }

        // Save settings
        $settings = $this->settingsService->saveSettings($validated);

        return new JsonResponse([
            'message' => trans('tochka-payment::app.admin.settings.index.update-success'),
            'settings' => $settings,
        ]);
    }
}
