<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Core\Models\CoreConfig;

class AlfabankSettingsController extends CustomerController
{
    /**
     * Get Alfabank payment settings for the current shop.
     */
    public function show(Request $request): Response
    {
        $this->resolveShopUser($request);

        $settings = $this->getSettingsFromCoreConfig();

        return response([
            'data' => $settings,
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
                : ($defaults[$key] ?? null);
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
            'min_order_total'              => '',
            'max_order_total'              => '',
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

