<?php

namespace Webkul\Reporting\Services;

use Webkul\Reporting\Models\AnalyticsDailyKpi;
use Illuminate\Support\Facades\DB;

class DailyKpiAggregator extends BaseAnalyticsService
{
    public function aggregateForDate(string $date, ?string $channel = null, ?int $locationId = null): void
    {
        $this->setDateRange(
            now()->parse($date)->startOfDay(),
            now()->parse($date)->endOfDay()
        );
        $this->channel = $channel;
        $this->locationId = $locationId;

        $northStar = app(NorthStarService::class);
        $northStar->setDateRange($this->startDate, $this->endDate);
        $northStar->setChannel($channel);
        $northStar->setLocationId($locationId);

        $funnel = app(FunnelRetentionService::class);
        $funnel->setDateRange($this->startDate, $this->endDate);
        $funnel->setChannel($channel);
        $funnel->setLocationId($locationId);

        $ops = app(OperationsService::class);
        $ops->setDateRange($this->startDate, $this->endDate);
        $ops->setChannel($channel);
        $ops->setLocationId($locationId);

        $payments = app(PaymentsChannelsService::class);
        $payments->setDateRange($this->startDate, $this->endDate);
        $payments->setChannel($channel);
        $payments->setLocationId($locationId);

        $onlineShare = $northStar->getOnlineOrderShare();
        $gmv = $northStar->getGMV();
        $aov = $northStar->getAOV();
        $sla = $northStar->getOrdersWithinSLA();
        $avgReady = $northStar->getAvgOrderReadyTime();
        $repeat = $northStar->getRepeatRate();
        $discount = $northStar->getDiscountedOrdersShare();

        $activeUsers = $funnel->getActiveUsers();
        $newUsers = $funnel->getNewUsers();
        $conversion = $funnel->getSessionToOrderConversion();

        $stages = $ops->getStageTimes();
        $cancelRefund = $ops->getCancelRefundRate();
        $incorrect = $ops->getIncorrectOrdersRate();

        $paymentRate = $payments->getPaymentSuccessRate();
        $complaints = $payments->getComplaintsStats();

        AnalyticsDailyKpi::updateOrCreate(
            [
                'date'        => $date,
                'channel'     => $channel,
                'location_id' => $locationId,
            ],
            [
                'total_orders'          => $onlineShare['total'],
                'online_orders'         => $onlineShare['online'],
                'online_order_share'    => $onlineShare['value'],
                'gmv'                   => $gmv['value'],
                'aov'                   => $aov['value'],
                'orders_within_sla'     => $sla['within'],
                'sla_pct'               => $sla['value'],
                'avg_order_ready_seconds' => $avgReady['seconds'],
                'repeat_customers'      => $repeat['repeat_customers'],
                'repeat_rate'           => $repeat['value'],

                'dau'                   => $activeUsers['dau'],
                'new_users'             => $newUsers['daily'],
                'sessions'              => $conversion['total'],
                'sessions_with_order'   => $conversion['with_order'],
                'session_to_order_rate' => $conversion['overall'],

                'discounted_orders'     => $discount['discounted_orders'],
                'discount_total'        => $discount['discount_total'],

                'avg_accept_seconds'    => $stages['order_to_accepted']['seconds'],
                'avg_prepare_seconds'   => $stages['accepted_to_ready']['seconds'],
                'avg_serve_seconds'     => $stages['ready_to_served']['seconds'],
                'incorrect_orders'      => $incorrect['count'],
                'cancelled_orders'      => $cancelRefund['cancelled'],
                'refunded_orders'       => $cancelRefund['refunded'],

                'payment_attempts'      => (int) ($paymentRate['by_method'][0]['total'] ?? 0),
                'payment_successes'     => (int) ($paymentRate['by_method'][0]['successes'] ?? 0),
                'payment_success_rate'  => $paymentRate['overall_rate'],

                'complaints'            => $complaints['total'],
                'incidents_resolved'    => $complaints['resolved'],
            ]
        );
    }

    public function aggregateYesterday(): void
    {
        $date = now()->subDay()->toDateString();

        $this->aggregateForDate($date);

        $channels = DB::table('orders')
            ->whereDate('created_at', $date)
            ->distinct('channel_name')
            ->pluck('channel_name');

        foreach ($channels as $channel) {
            $this->aggregateForDate($date, $channel);
        }
    }
}
