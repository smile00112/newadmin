<?php

namespace Webkul\TochkaPayment\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CompanyRepository;
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
     * Company repository instance.
     *
     * @var \Webkul\Newsletters\Repositories\CompanyRepository
     */
    protected $companyRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(SettingsService $settingsService, CompanyRepository $companyRepository)
    {
        $this->settingsService = $settingsService;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Display the settings form.
     */
    public function index(Request $request): View
    {
        $admin = auth()->guard('admin')->user();
        $companies = collect();
        $companyId = null;

        if ($admin) {
            if ($admin->role && $admin->role->permission_type === 'all' && !$admin->company_id) {
                $companies = $this->companyRepository->all();
                $companyId = $request->integer('company_id', 0) ?: null;
                if (!$companyId && $companies->isNotEmpty()) {
                    $companyId = (int) $companies->first()->id;
                }
            } elseif ($admin->company_id) {
                $company = $this->companyRepository->find($admin->company_id);
                if ($company) {
                    $companies = collect([$company]);
                    $companyId = (int) $admin->company_id;
                }
            }
        }

        $settings = $companyId
            ? $this->settingsService->getSettingsModel($companyId)
            : $this->settingsService->getSettingsModel();

        if (!$settings) {
            $settings = new \Webkul\TochkaPayment\Models\TochkaPaymentSettings();
            if ($companyId) {
                $settings->company_id = $companyId;
            }
        }

        return view('tochka-payment::admin.settings.index', compact('settings', 'companies'));
    }

    /**
     * Get settings for a company (superuser only). Used when switching company in the form.
     */
    public function getByCompany(int $companyId): JsonResponse
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin || !$admin->role || $admin->role->permission_type !== 'all' || $admin->company_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $settings = $this->settingsService->getSettingsModel($companyId);

        $paymentMode = $settings?->payment_mode ?? ['sbp', 'card'];
        if (is_array($paymentMode)) {
            $paymentMode = implode(',', $paymentMode);
        }

        return new JsonResponse([
            'company_id' => $companyId,
            'client_id' => $settings?->client_id ?? '',
            'jwt_token' => $settings?->jwt_token ?? '',
            'api_base_url' => $settings?->api_base_url ?? '',
            'webhook_url' => $settings?->webhook_url ?? '',
            'customer_code' => $settings?->customer_code ?? '',
            'merchant_id' => $settings?->merchant_id ?? '',
            'payment_mode' => $paymentMode,
            'save_card' => (bool) ($settings?->save_card ?? false),
            'pre_authorization' => (bool) ($settings?->pre_authorization ?? false),
            'ttl' => (int) ($settings?->ttl ?? 10080),
            'min_amount' => (float) ($settings?->min_amount ?? 1.0),
            'is_active' => (bool) ($settings?->is_active ?? false),
            'telegram_bot_token' => $settings?->telegram_bot_token ?? '',
            'telegram_chat_id' => $settings?->telegram_chat_id ?? '',
        ]);
    }

    /**
     * Store or update settings.
     */
    public function store(Request $request): JsonResponse
    {
        // Resolve company_id: from request (for super-admin) or from current admin
        $admin = auth()->guard('admin')->user();
        $companyId = $request->input('company_id');
        if ($companyId !== null) {
            $companyId = (int) $companyId;
        } else {
            $companyId = $admin?->company_id;
        }

        if (!$companyId) {
            return new JsonResponse([
                'message' => trans('tochka-payment::app.admin.settings.index.company_required'),
            ], 422);
        }

        //transform on/off to boolean
        $request['is_active'] = $request->boolean('is_active');
        $request['save_card'] = $request->boolean('save_card');

        $validated = $request->validate([
            'company_id' => 'nullable|integer|exists:companies,id',
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
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:255',
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

        unset($validated['company_id']);

        // Save settings for the resolved company
        $settings = $this->settingsService->saveSettings($validated, $companyId);

        return new JsonResponse([
            'message' => trans('tochka-payment::app.admin.settings.index.update-success'),
            'settings' => $settings,
        ]);
    }
}
