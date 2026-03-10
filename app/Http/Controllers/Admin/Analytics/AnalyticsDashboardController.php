<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\FunnelRetentionService;
use App\Services\Analytics\MenuAnalyticsService;
use App\Services\Analytics\NorthStarService;
use App\Services\Analytics\OperationsService;
use App\Services\Analytics\PaymentsChannelsService;
use App\Services\Analytics\VisitBehaviorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsDashboardController extends Controller
{
    public function __construct(
        protected NorthStarService $northStar,
        protected FunnelRetentionService $funnelRetention,
        protected OperationsService $operations,
        protected MenuAnalyticsService $menuAnalytics,
        protected PaymentsChannelsService $paymentsChannels,
        protected VisitBehaviorService $visitBehavior,
    ) {}

    // ─── EXECUTIVE DASHBOARD ───────────────────────────────────────

    public function executive()
    {
        return view('admin::analytics.executive');
    }

    public function executiveStats(): JsonResponse
    {
        $this->northStar->setFiltersFromRequest();
        $this->funnelRetention->setFiltersFromRequest();
        $this->paymentsChannels->setFiltersFromRequest();

        return response()->json([
            'north_star'    => $this->northStar->getAll(),
            'active_users'  => $this->funnelRetention->getActiveUsers(),
            'revenue_per_user' => $this->funnelRetention->getRevenuePerUser(),
            'channel_split' => $this->paymentsChannels->getChannelSplit(),
            'payment_rate'  => $this->paymentsChannels->getPaymentSuccessRate(),
        ]);
    }

    // ─── DAILY MANAGEMENT DASHBOARD (K) ────────────────────────────

    public function daily()
    {
        return view('admin::analytics.daily');
    }

    public function dailyStats(): JsonResponse
    {
        $this->northStar->setFiltersFromRequest();
        $this->funnelRetention->setFiltersFromRequest();
        $this->operations->setFiltersFromRequest();
        $this->menuAnalytics->setFiltersFromRequest();
        $this->paymentsChannels->setFiltersFromRequest();

        return response()->json([
            'online_order_share' => $this->northStar->getOnlineOrderShare(),
            'gmv'                => $this->northStar->getGMV(),
            'sla'                => $this->northStar->getOrdersWithinSLA(),
            'repeat_rate'        => $this->northStar->getRepeatRate(),
            'orders_per_user'    => $this->funnelRetention->getOrdersPerUser(),
            'aov'                => $this->northStar->getAOV(),
            'payment_rate'       => $this->paymentsChannels->getPaymentSuccessRate(),
            'top_dishes'         => $this->menuAnalytics->getTopDishesByQuantity(5),
            'attach_rate'        => $this->menuAnalytics->getDrinkDessertAttachRate(),
            'complaints'         => $this->paymentsChannels->getComplaintsStats(),
            'kiosk_uptime'       => $this->paymentsChannels->getKioskUptime(),
        ]);
    }

    // ─── PRODUCT ANALYTICS DASHBOARD ───────────────────────────────

    public function product()
    {
        return view('admin::analytics.product');
    }

    public function productStats(): JsonResponse
    {
        $this->funnelRetention->setFiltersFromRequest();
        $this->visitBehavior->setFiltersFromRequest();
        $this->menuAnalytics->setFiltersFromRequest();

        return response()->json([
            'funnel'              => $this->funnelRetention->getFunnelDropoff(),
            'conversion'          => $this->funnelRetention->getSessionToOrderConversion(),
            'time_to_payment'     => $this->funnelRetention->getTimeToPayment(),
            'retention'           => $this->funnelRetention->getCohortRetention(),
            'orders_per_user'     => $this->funnelRetention->getOrdersPerUser(),
            'median_tbo'          => $this->funnelRetention->getMedianTimeBetweenOrders(),
            'arpu_rppu'           => $this->funnelRetention->getRevenuePerUser(),
            'first_order_mix'     => $this->visitBehavior->getOrderMix(1),
            'second_order_mix'    => $this->visitBehavior->getOrderMix(2),
            'aov_by_visit'        => $this->visitBehavior->getAovByVisit(),
            'repeat_dish_rate'    => $this->visitBehavior->getRepeatDishRate(),
            'category_transition' => $this->visitBehavior->getCategoryTransitionMap(),
        ]);
    }

    // ─── OPERATIONS DASHBOARD ──────────────────────────────────────

    public function operations()
    {
        return view('admin::analytics.operations');
    }

    public function operationsStats(): JsonResponse
    {
        $this->operations->setFiltersFromRequest();
        $this->paymentsChannels->setFiltersFromRequest();

        return response()->json([
            'stage_times'      => $this->operations->getStageTimes(),
            'incorrect_orders' => $this->operations->getIncorrectOrdersRate(),
            'cancel_refund'    => $this->operations->getCancelRefundRate(),
            'heatmap'          => $this->operations->getOrderHeatmap(),
            'handoff_delays'   => $this->operations->getHandoffDelays(),
            'payment_rate'     => $this->paymentsChannels->getPaymentSuccessRate(),
            'fail_reasons'     => $this->paymentsChannels->getPaymentFailReasons(),
            'crash_free'       => $this->paymentsChannels->getCrashFreeSessions(),
            'screen_latency'   => $this->paymentsChannels->getAvgScreenLatency(),
            'complaints'       => $this->paymentsChannels->getComplaintsStats(),
            'nps'              => $this->paymentsChannels->getNPS(),
            'kiosk_uptime'     => $this->paymentsChannels->getKioskUptime(),
        ]);
    }

    // ─── MENU ANALYTICS DASHBOARD ──────────────────────────────────

    public function menu()
    {
        return view('admin::analytics.menu');
    }

    public function menuStats(): JsonResponse
    {
        $this->menuAnalytics->setFiltersFromRequest();

        return response()->json([
            'top_by_revenue'      => $this->menuAnalytics->getTopDishesByRevenue(),
            'top_by_quantity'     => $this->menuAnalytics->getTopDishesByQuantity(),
            'attach_rate'         => $this->menuAnalytics->getDrinkDessertAttachRate(),
            'customization_rate'  => $this->menuAnalytics->getCustomizationRate(),
            'top_added'           => $this->menuAnalytics->getTopIngredients('added'),
            'top_removed'         => $this->menuAnalytics->getTopIngredients('removed'),
            'new_dish_metrics'    => $this->menuAnalytics->getNewDishMetrics(),
            'dead_items'          => $this->menuAnalytics->getDeadItems(),
            'aov_uplift'          => $this->menuAnalytics->getAovUplift(),
        ]);
    }

    // ─── CHANNELS DASHBOARD ────────────────────────────────────────

    public function channels()
    {
        return view('admin::analytics.channels');
    }

    public function channelsStats(): JsonResponse
    {
        $this->paymentsChannels->setFiltersFromRequest();
        $this->northStar->setFiltersFromRequest();

        return response()->json([
            'channel_split'    => $this->paymentsChannels->getChannelSplit(),
            'dine_vs_takeaway' => $this->paymentsChannels->getDineInVsTakeaway(),
            'by_location'      => $this->paymentsChannels->getOrdersByLocation(),
            'post_rating'      => $this->paymentsChannels->getPostOrderRating(),
            'nps'              => $this->paymentsChannels->getNPS(),
            'complaints'       => $this->paymentsChannels->getComplaintsStats(),
            'revenue_by_channel' => $this->northStar->getRevenueByChannel(),
        ]);
    }
}
