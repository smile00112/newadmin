<?php

namespace Webkul\Reporting\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

abstract class BaseAnalyticsService
{
    protected Carbon $startDate;

    protected Carbon $endDate;

    protected ?string $channel = null;

    protected ?int $locationId = null;

    public function __construct()
    {
        $this->startDate = now()->subDays(30)->startOfDay();
        $this->endDate = now()->endOfDay();
    }

    public function setDateRange(Carbon $start, Carbon $end): static
    {
        $this->startDate = $start->startOfDay();
        $this->endDate = $end->endOfDay();

        return $this;
    }

    public function setChannel(?string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function setLocationId(?int $locationId): static
    {
        $this->locationId = $locationId;

        return $this;
    }

    public function setFiltersFromRequest(): static
    {
        $this->startDate = request()->date('start') ?? now()->subDays(30);
        $this->startDate = $this->startDate->startOfDay();

        $this->endDate = request()->date('end') ?? now();
        $this->endDate = $this->endDate->endOfDay();

        $this->channel = request()->query('channel');
        $this->locationId = request()->query('location_id') ? (int) request()->query('location_id') : null;

        return $this;
    }

    protected function applyDimensionFilters($query, string $channelCol = 'channel', string $locationCol = 'location_id')
    {
        if ($this->channel) {
            $query->where($channelCol, $this->channel);
        }

        if ($this->locationId) {
            $query->where($locationCol, $this->locationId);
        }

        return $query;
    }

    protected function percentChange(float|int|null $current, float|int|null $previous): float
    {
        $current = (float) ($current ?? 0);
        $previous = (float) ($previous ?? 0);

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    protected function safeDiv(float|int|null $numerator, float|int|null $denominator, int $decimals = 4): float
    {
        $numerator = (float) ($numerator ?? 0);
        $denominator = (float) ($denominator ?? 0);

        return $denominator > 0 ? round($numerator / $denominator, $decimals) : 0;
    }

    protected function previousPeriodDates(): array
    {
        $days = $this->startDate->diffInDays($this->endDate);

        return [
            $this->startDate->copy()->subDays($days + 1)->startOfDay(),
            $this->startDate->copy()->subDay()->endOfDay(),
        ];
    }
}
