<?php

namespace Webkul\AlfabankPayment\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Models\CoreConfig;

class SettingsController extends Controller
{
    /**
     * Display the Alfabank Payment settings page.
     */
    public function index(): View
    {
        $settings = $this->getSettingsFromCoreConfig();

        return view('alfabank-payment::admin.settings.index', compact('settings'));
    }

    /**
     * Update the Alfabank Payment settings.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'active'                       => 'nullable|boolean',
            'title'                        => 'nullable|string|max:255',
            'description'                  => 'nullable|string',
            'merchant'                     => 'nullable|string|max:255',
            'password'                     => 'nullable|string|max:255',
            'token'                        => 'nullable|string|max:255',
            'test_mode'                    => 'nullable|boolean',
            'stage_mode'                   => 'nullable|in:one-stage,two-stage',
            'order_status_paid'            => 'nullable|string',
            'success_url'                  => 'nullable|url|max:512',
            'fail_url'                     => 'nullable|url|max:512',
            'send_order'                   => 'nullable|boolean',
            'tax_system'                   => 'nullable|integer|in:0,1,2,3,4,5',
            'tax_type'                     => 'nullable|integer',
            'version_ffd'                  => 'nullable|in:v1_05,v1_2',
            'payment_method_type'          => 'nullable|integer|in:1,2,3,4,5,6,7',
            'payment_object_type'          => 'nullable|integer|in:1,2,3,4,5,7,9,10,11,12,13',
            'payment_object_type_delivery' => 'nullable|integer|in:1,2,3,4,5,6,7',
            'saved_cards_payment_enable'   => 'nullable|boolean',
            'min_order_total'             => 'nullable|numeric|min:0',
            'max_order_total'             => 'nullable|numeric|min:0',
            'callback_type'                => 'nullable|in:STATIC,DYNAMIC',
        ]);

        foreach ($this->getConfigKeys() as $key) {
            $value = $validated[$key] ?? null;

            // Convert boolean to string for storage
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            // Convert null to empty string
            if ($value === null) {
                $value = '';
            } else {
                $value = (string) $value;
            }

            $code = 'sales.payment_methods.alfabank.' . $key;
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
            'message' => trans('admin::app.configuration.save-message'),
        ]);
    }

    /**
     * Get settings from core_config with fallback to defaults.
     *
     * @return array<string, mixed>
     */
    protected function getSettingsFromCoreConfig(): array
    {
        $defaults = $this->getDefaultSettings();
        $settings = [];

        foreach ($this->getConfigKeys() as $key) {
            $code = 'sales.payment_methods.alfabank.' . $key;
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

    /**
     * Get default settings.
     *
     * @return array<string, mixed>
     */
    protected function getDefaultSettings(): array
    {
        return [
            'active'                       => '0',
            'title'                        => 'Альфа-Банк',
            'description'                  => 'Оплата картой через Альфа-Банк',
            'merchant'                     => '',
            'password'                     => '',
            'token'                        => '',
            'test_mode'                    => '1',
            'stage_mode'                   => 'one-stage',
            'order_status_paid'            => 'processing',
            'success_url'                  => '',
            'fail_url'                     => '',
            'send_order'                   => '0',
            'tax_system'                   => '0',
            'tax_type'                     => '0',
            'version_ffd'                  => 'v1_05',
            'payment_method_type'          => '4',
            'payment_object_type'          => '1',
            'payment_object_type_delivery' => '1',
            'saved_cards_payment_enable'   => '0',
            'min_order_total'             => '',
            'max_order_total'             => '',
            'callback_type'                => 'STATIC',
        ];
    }

    /**
     * Get config keys list.
     *
     * @return array<string>
     */
    protected function getConfigKeys(): array
    {
        return [
            'active',
            'title',
            'description',
            'merchant',
            'password',
            'token',
            'test_mode',
            'stage_mode',
            'order_status_paid',
            'success_url',
            'fail_url',
            'send_order',
            'tax_system',
            'tax_type',
            'version_ffd',
            'payment_method_type',
            'payment_object_type',
            'payment_object_type_delivery',
            'saved_cards_payment_enable',
            'min_order_total',
            'max_order_total',
            'callback_type',
        ];
    }
}
