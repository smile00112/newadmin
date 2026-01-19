<?php

namespace Webkul\RestApi\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\RestApi\Config\AuthChannelFieldsConfig;
use Webkul\RestApi\Repositories\AuthChannelSettingRepository;

class AuthChannelSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AuthChannelSettingRepository $settingRepository,
        protected AuthChannelFieldsConfig $fieldsConfig
    ) {}

    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $channelCode = request('channel', core()->getDefaultChannelCode());
        $selectedChannel = request('auth_channel', 'sms');
        
        $channels = $this->fieldsConfig->getChannels();
        $fields = $this->settingRepository->getSettingsWithFields($selectedChannel, $channelCode);

        return view('rest-api::admin.auth-channel-settings.index', compact(
            'fields',
            'channelCode',
            'selectedChannel',
            'channels'
        ));
    }

    /**
     * Store settings.
     */
    public function store(): RedirectResponse
    {
        $channelCode = request('channel_code');
        $authChannel = request('auth_channel');
        $settings = request('settings', []);

        // Clean test phone numbers if present
        if (isset($settings['test_phone_numbers'])) {
            $settings['test_phone_numbers'] = $this->cleanPhoneNumbers($settings['test_phone_numbers']);
        }

        $this->settingRepository->saveSettings($authChannel, $settings, $channelCode);

        session()->flash('success', trans('rest-api::app.auth_channels.settings.save-success'));

        return redirect()->back();
    }

    /**
     * Clean phone numbers from extra characters.
     * Keeps only digits and preserves line breaks as separators.
     */
    protected function cleanPhoneNumbers(string $phoneNumbers): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $phoneNumbers);
        $cleanedLines = [];

        foreach ($lines as $line) {
            $cleaned = preg_replace('/[^0-9]/', '', trim($line));

            if (!empty($cleaned)) {
                $cleanedLines[] = $cleaned;
            }
        }

        return implode("\n", $cleanedLines);
    }

    /**
     * Get settings for a specific channel via API.
     */
    public function getSettings(string $channel): Response
    {
        $channelCode = request('channel_code', core()->getDefaultChannelCode());
        $settings = $this->settingRepository->getAllSettings($channel, $channelCode);

        return response()->json([
            'data' => $settings,
        ]);
    }
}
