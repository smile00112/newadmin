<?php

namespace Webkul\TochkaPayment\Services;

use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Models\TochkaPaymentSettingsProxy;

class SettingsService
{
    /**
     * Get settings for current company with fallback to config.
     *
     * @param  int|null  $companyId
     * @return array
     */
    public function getSettings($companyId = null): array
    {
       // dd($companyId);
        if ($companyId === null) {
            $admin = auth()->guard('admin')->user();
            $companyId = $admin?->company_id;
        }else{
            $companyId = (int) $companyId;
        }



        if (!$companyId) {
            // Fallback to config for backward compatibility
            return $this->getConfigSettings();
        }

        $settings = TochkaPaymentSettingsProxy::forCompany($companyId)->first();

        if (!$settings) {
            return $this->getConfigSettings();
        }

        return [
            'client_id' => $settings->client_id ?: config('tochka-payment.client_id'),
            'jwt_token' => $settings->jwt_token ?: config('tochka-payment.bearer_token'),
            'api_base_url' => $settings->api_base_url ?: config('tochka-payment.api_base_url'),
            'webhook_url' => $settings->webhook_url ?: config('tochka-payment.webhook_url'),
            'customer_code' => $settings->customer_code ?: config('tochka-payment.customer_code'),
            'merchant_id' => $settings->merchant_id ?: config('tochka-payment.merchant_id'),
            'consumer_id' => $settings->consumer_id ?: config('tochka-payment.consumer_id'),
            'payment_mode' => $settings->payment_mode ?: $this->parsePaymentMode(config('tochka-payment.payment_mode')),
            'save_card' => $settings->save_card ?? config('tochka-payment.save_card', false),
            'pre_authorization' => $settings->pre_authorization ?? config('tochka-payment.pre_authorization', false),
            'ttl' => $settings->ttl ?? config('tochka-payment.ttl', 10080),
            'min_amount' => $settings->min_amount ?? config('tochka-payment.min_amount', 1.00),
            'is_active' => $settings->is_active ?? false,
            'company_id' => $companyId,
        ];
    }

    /**
     * Get settings model for current company.
     *
     * @param  int|null  $companyId
     * @return \Webkul\TochkaPayment\Models\TochkaPaymentSettings|null
     */
    public function getSettingsModel(?int $companyId = null)
    {
        if ($companyId === null) {
            $admin = auth()->guard('admin')->user();
            $companyId = $admin?->company_id;
        }

        if (!$companyId) {
            return null;
        }

        return TochkaPaymentSettingsProxy::forCompany($companyId)->first();
    }

    /**
     * Save or update settings for current company.
     *
     * @param  array  $data
     * @param  int|null  $companyId
     * @return \Webkul\TochkaPayment\Models\TochkaPaymentSettings
     */
    public function saveSettings(array $data, ?int $companyId = null)
    {
        if ($companyId === null) {
            $admin = auth()->guard('admin')->user();
            $companyId = $admin?->company_id;
        }

        if (!$companyId) {
            throw new \Exception('Company ID is required to save settings');
        }

        // Parse payment_mode if it's a string
        if (isset($data['payment_mode']) && is_string($data['payment_mode'])) {
            $data['payment_mode'] = $this->parsePaymentMode($data['payment_mode']);
        }

        $settings = TochkaPaymentSettingsProxy::updateOrCreate(
            ['company_id' => $companyId],
            $data
        );

        Log::info('Tochka Payment: Settings saved', [
            'company_id' => $companyId,
            'is_active' => $settings->is_active,
        ]);

        return $settings;
    }

    /**
     * Update consumer ID for current company.
     *
     * @param  string  $consumerId
     * @param  int|null  $companyId
     * @return bool
     */
    public function updateConsumerId(string $consumerId, ?int $companyId = null): bool
    {
        if ($companyId === null) {
            $admin = auth()->guard('admin')->user();
            $companyId = $admin?->company_id;
        }

        if (!$companyId) {
            return false;
        }

        $settings = TochkaPaymentSettingsProxy::forCompany($companyId)->first();

        if (!$settings) {
            return false;
        }

        $settings->update(['consumer_id' => $consumerId]);

        Log::info('Tochka Payment: Consumer ID updated', [
            'company_id' => $companyId,
            'consumer_id' => $consumerId,
        ]);

        return true;
    }

    /**
     * Validate settings data.
     *
     * @param  array  $data
     * @return array
     */
    public function validateSettings(array $data): array
    {
        $errors = [];

        if (isset($data['api_base_url']) && !filter_var($data['api_base_url'], FILTER_VALIDATE_URL)) {
            $errors['api_base_url'] = 'Invalid API base URL';
        }

        if (isset($data['webhook_url']) && !empty($data['webhook_url']) && !filter_var($data['webhook_url'], FILTER_VALIDATE_URL)) {
            $errors['webhook_url'] = 'Invalid webhook URL';
        }

        if (isset($data['min_amount']) && $data['min_amount'] < 0.01) {
            $errors['min_amount'] = 'Minimum amount must be at least 0.01';
        }

        if (isset($data['ttl']) && ($data['ttl'] < 1 || $data['ttl'] > 43200)) {
            $errors['ttl'] = 'TTL must be between 1 and 43200 minutes';
        }

        return $errors;
    }

    /**
     * Get settings from config (fallback).
     *
     * @return array
     */
    protected function getConfigSettings(): array
    {
        return [
            'client_id' => config('tochka-payment.client_id'),
            'jwt_token' => config('tochka-payment.bearer_token'),
            'api_base_url' => config('tochka-payment.api_base_url'),
            'webhook_url' => config('tochka-payment.webhook_url'),
            'customer_code' => config('tochka-payment.customer_code'),
            'merchant_id' => config('tochka-payment.merchant_id'),
            'consumer_id' => config('tochka-payment.consumer_id'),
            'payment_mode' => $this->parsePaymentMode(config('tochka-payment.payment_mode')),
            'save_card' => config('tochka-payment.save_card', false),
            'pre_authorization' => config('tochka-payment.pre_authorization', false),
            'ttl' => config('tochka-payment.ttl', 10080),
            'min_amount' => config('tochka-payment.min_amount', 1.00),
            'is_active' => false,
            'company_id' => null,
        ];
    }

    /**
     * Parse payment mode string to array.
     *
     * @param  string|array  $paymentMode
     * @return array
     */
    protected function parsePaymentMode($paymentMode): array
    {
        if (is_array($paymentMode)) {
            return $paymentMode;
        }

        if (empty($paymentMode)) {
            return ['sbp', 'card'];
        }

        return array_map('trim', explode(',', $paymentMode));
    }
}
