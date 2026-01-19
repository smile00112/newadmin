<?php

namespace Webkul\IikoIntegration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\IikoIntegration\Config\IikoFieldsConfig;
use Webkul\IikoIntegration\Repositories\IikoSettingRepository;
use Webkul\IikoIntegration\Services\IikoAuthService;

class IikoSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected IikoSettingRepository $settingRepository,
        protected IikoFieldsConfig $fieldsConfig,
        protected IikoAuthService $authService
    ) {}

    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $channelCode = request('channel', core()->getDefaultChannelCode());
        $fields = $this->settingRepository->getSettingsWithFields($channelCode);

        return view('iiko-integration::admin.iiko.settings', compact(
            'fields',
            'channelCode'
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
        $this->settingRepository->clearCache($channelCode);
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
}
