<?php

namespace Webkul\Reporting\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Reporting\Services\DailyKpiAggregator;

class AggregateDailyKpi extends Command
{
    protected $signature = 'analytics:aggregate-daily {--date= : Specific date (Y-m-d), defaults to yesterday}';

    protected $description = 'Aggregate daily KPI metrics for the analytics dashboard';

    public function handle(DailyKpiAggregator $aggregator): int
    {
        $date = $this->option('date') ?? now()->subDay()->toDateString();

        $this->info("Aggregating KPIs for {$date}...");

        $aggregator->aggregateForDate($date);

        $this->info('Done.');

        return self::SUCCESS;
    }
}
