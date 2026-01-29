<?php

namespace Webkul\Bonus\Repositories;

use Illuminate\Container\Container;
use Webkul\Bonus\Models\BonusSetting;
use Webkul\Core\Repositories\AbstractSettingRepository;

class BonusSettingRepository extends AbstractSettingRepository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return BonusSetting::class;
    }

    /**
     * Get cache prefix.
     *
     * @return string
     */
    protected function getCachePrefix(): string
    {
        return 'bonus_settings';
    }

    /**
     * Get group column name.
     *
     * @return string
     */
    protected function getGroupColumn(): string
    {
        return 'channel';
    }

    /**
     * Get bonus system enabled status.
     *
     * @param  string|null  $channelCode
     * @return bool
     */
    public function isBonusEnabled(?string $channelCode = null): bool
    {
        return (bool) $this->getSetting('bonus', 'enabled', $channelCode);
    }

    /**
     * Get max usage percent.
     *
     * @param  string|null  $channelCode
     * @return float
     */
    public function getMaxUsagePercent(?string $channelCode = null): float
    {
        return (float) ($this->getSetting('bonus', 'max_usage_percent', $channelCode) ?? 100);
    }

    /**
     * Get expiry days.
     *
     * @param  string|null  $channelCode
     * @return int
     */
    public function getExpiryDays(?string $channelCode = null): int
    {
        return (int) ($this->getSetting('bonus', 'expiry_days', $channelCode) ?? 365);
    }

    /**
     * Get participating product IDs.
     *
     * @param  string|null  $channelCode
     * @return array
     */
    public function getParticipatingProductIds(?string $channelCode = null): array
    {
        $ids = $this->getSetting('bonus', 'participating_product_ids', $channelCode);

        if (empty($ids)) {
            return [];
        }

        return is_array($ids) ? $ids : json_decode($ids, true) ?? [];
    }

    /**
     * Get excluded product IDs.
     *
     * @param  string|null  $channelCode
     * @return array
     */
    public function getExcludedProductIds(?string $channelCode = null): array
    {
        $ids = $this->getSetting('bonus', 'excluded_product_ids', $channelCode);

        if (empty($ids)) {
            return [];
        }

        return is_array($ids) ? $ids : json_decode($ids, true) ?? [];
    }
}
