<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
$start = Carbon::parse('2024-01-01');
$end = Carbon::parse('2026-03-10');

echo "=== NorthStar Service ===" . PHP_EOL;
$ns = app(App\Services\Analytics\NorthStarService::class);
$ns->setDateRange($start, $end);
$data = $ns->getAll();
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "=== Funnel/Retention ===" . PHP_EOL;
$fr = app(App\Services\Analytics\FunnelRetentionService::class);
$fr->setDateRange($start, $end);
echo "Funnel: " . json_encode($fr->getFunnelDropoff(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo "Conversion: " . json_encode($fr->getSessionToOrderConversion(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo "Retention: " . json_encode($fr->getCohortRetention(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "=== Menu Analytics ===" . PHP_EOL;
$menu = app(App\Services\Analytics\MenuAnalyticsService::class);
$menu->setDateRange($start, $end);
echo "Top by revenue: " . json_encode($menu->getTopDishesByRevenue(5), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo "Attach rate: " . json_encode($menu->getDrinkDessertAttachRate(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "=== Operations ===" . PHP_EOL;
$ops = app(App\Services\Analytics\OperationsService::class);
$ops->setDateRange($start, $end);
echo "Stage times: " . json_encode($ops->getStageTimes(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo "Cancel/Refund: " . json_encode($ops->getCancelRefundRate(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "=== Payments/Channels ===" . PHP_EOL;
$pc = app(App\Services\Analytics\PaymentsChannelsService::class);
$pc->setDateRange($start, $end);
echo "Channel split: " . json_encode($pc->getChannelSplit(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo "Payment rate: " . json_encode($pc->getPaymentSuccessRate(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo "NPS: " . json_encode($pc->getNPS(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo "Kiosk uptime: " . json_encode($pc->getKioskUptime(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "=== Visit Behavior ===" . PHP_EOL;
$vb = app(App\Services\Analytics\VisitBehaviorService::class);
$vb->setDateRange($start, $end);
echo "Order mix 1st: " . json_encode($vb->getOrderMix(1), JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo "AOV by visit: " . json_encode($vb->getAovByVisit(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "=== DB Tables Check ===" . PHP_EOL;
echo "analytics_events: " . \DB::table('analytics_events')->count() . PHP_EOL;
echo "analytics_sessions: " . \DB::table('analytics_sessions')->count() . PHP_EOL;
echo "analytics_order_timestamps: " . \DB::table('analytics_order_timestamps')->count() . PHP_EOL;
echo "analytics_payment_attempts: " . \DB::table('analytics_payment_attempts')->count() . PHP_EOL;
echo "analytics_incidents: " . \DB::table('analytics_incidents')->count() . PHP_EOL;
echo "analytics_kiosk_status: " . \DB::table('analytics_kiosk_status')->count() . PHP_EOL;
echo "analytics_daily_kpi: " . \DB::table('analytics_daily_kpi')->count() . PHP_EOL;
echo "orders: " . \DB::table('orders')->count() . PHP_EOL;
echo "order_items: " . \DB::table('order_items')->count() . PHP_EOL;
echo "customers: " . \DB::table('customers')->count() . PHP_EOL;
