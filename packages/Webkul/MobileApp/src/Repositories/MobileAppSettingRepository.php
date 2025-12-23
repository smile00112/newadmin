<?php

namespace Webkul\MobileApp\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\MobileApp\Config\FieldsConfig;
use Webkul\MobileApp\Contracts\MobileAppSetting;

class MobileAppSettingRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return MobileAppSetting::class;
    }

    /**
     * Get all settings as key-value pairs.
     */
    public function getAllSettings(?string $channelCode = null): array
    {
        $query = $this->model->query();

        if ($channelCode) {
            $query->where('channel_code', $channelCode);
        }

        $settings = $query->get();

        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Get a specific setting value.
     */
    public function getSetting(string $key, ?string $channelCode = null): mixed
    {
        $query = $this->model->query()->where('key', $key);

        if ($channelCode) {
            $query->where('channel_code', $channelCode);
        }

        $setting = $query->first();

        return $setting?->value;
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, mixed $value, ?string $channelCode = null): void
    {
        $this->updateOrCreate(
            [
                'key'          => $key,
                'channel_code' => $channelCode,
            ],
            [
                'value' => $value,
            ]
        );
    }

    /**
     * Save multiple settings at once.
     */
    public function saveSettings(array $settings, ?string $channelCode = null): void
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($key, $value, $channelCode);
        }
    }

    /**
     * Get settings with field definitions for admin form.
     */
    public function getSettingsWithFields(?string $channelCode = null): array
    {
        $fieldsConfig = app(FieldsConfig::class);
        $fields = $fieldsConfig->getFields();
        $settings = $this->getAllSettings($channelCode);

        foreach ($fields as &$field) {
            $field['value'] = $settings[$field['key']] ?? ($field['default'] ?? null);

            if (isset($field['source'])) {
                $field['options'] = $fieldsConfig->getOptionsForSource($field['source']);
            }
        }

        return $fields;
    }
}


