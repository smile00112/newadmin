<?php

namespace Webkul\TochkaPayment\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Models\CoreConfig;

class SettingsController extends Controller
{
    /**
     * Config keys stored in core_config (without prefix).
     *
     * @var array<string>
     */
    protected static array $configKeys = [
        'server_url',
        'login',
        'secret_key',
        'webhook_url',
        'api_token',
        'min_amount',
        'service_name',
        'lang',
    ];

    /**
     * Display the Tochka Payment settings page.
     */
    public function index(): View
    {
        $settings = $this->getSettingsFromCoreConfig();

        return view('tochka-payment::admin.settings.index', compact('settings'));
    }

    /**
     * Update the Tochka Payment settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_url'   => 'required|url',
            'login'        => 'required|string|max:255',
            'secret_key'   => 'required|string|max:255',
            'webhook_url'  => 'nullable|url|max:500',
            'api_token'    => 'nullable|string|max:255',
            'min_amount'   => 'required|numeric|min:0',
            'service_name' => 'required|string|max:255',
            'lang'         => 'required|in:ru,en',
        ]);

        foreach (self::$configKeys as $key) {
            $value = $validated[$key] ?? '';
            $value = $value === null ? '' : (string) $value;

            $code = 'tochka_payment.'.$key;
            $existing = CoreConfig::where('code', $code)
                ->whereNull('channel_code')
                ->whereNull('locale_code')
                ->first();

            if ($existing) {
                $existing->update(['value' => $value]);
            } else {
                CoreConfig::create([
                    'code'         => $code,
                    'value'        => $value,
                    'channel_code' => null,
                    'locale_code'  => null,
                ]);
            }
        }

        return new JsonResponse([
            'message' => trans('tochka-payment::app.admin.settings.index.update-success'),
        ]);
    }

    /**
     * Get settings from core_config with fallback to config file.
     *
     * @return array<string, string|float>
     */
    protected function getSettingsFromCoreConfig(): array
    {
        $defaults = config('tochka-payment', []);
        $settings = [];

        foreach (self::$configKeys as $key) {
            $code = 'tochka_payment.'.$key;
            $record = CoreConfig::where('code', $code)
                ->whereNull('channel_code')
                ->whereNull('locale_code')
                ->first();

            $settings[$key] = $record !== null
                ? $record->value
                : ($defaults[$key] ?? '');
        }

        return $settings;
    }
}
