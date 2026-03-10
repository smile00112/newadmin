<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

echo "=== ORDERS columns ===" . PHP_EOL;
$cols = DB::select('SHOW COLUMNS FROM orders');
foreach ($cols as $c) echo $c->Field . ' (' . $c->Type . ')' . PHP_EOL;

echo PHP_EOL . "=== ORDER_ITEMS columns ===" . PHP_EOL;
$cols = DB::select('SHOW COLUMNS FROM order_items');
foreach ($cols as $c) echo $c->Field . ' (' . $c->Type . ')' . PHP_EOL;

echo PHP_EOL . "=== Sample order statuses ===" . PHP_EOL;
$statuses = DB::table('orders')->select('status', DB::raw('count(*) as cnt'))->groupBy('status')->get();
foreach ($statuses as $s) echo $s->status . ': ' . $s->cnt . PHP_EOL;

echo PHP_EOL . "=== Sample channel_name values ===" . PHP_EOL;
$channels = DB::table('orders')->select('channel_name', DB::raw('count(*) as cnt'))->groupBy('channel_name')->get();
foreach ($channels as $c) echo ($c->channel_name ?: 'NULL') . ': ' . $c->cnt . PHP_EOL;

echo PHP_EOL . "=== order_payment table exists? ===" . PHP_EOL;
try {
    $cnt = DB::table('order_payment')->count();
    echo "order_payment: " . $cnt . " rows" . PHP_EOL;
    $cols = DB::select('SHOW COLUMNS FROM order_payment');
    foreach ($cols as $c) echo $c->Field . ' (' . $c->Type . ')' . PHP_EOL;
} catch (\Exception $e) {
    echo "order_payment: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Sample order dates ===" . PHP_EOL;
$dates = DB::table('orders')->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')->first();
echo "From: " . $dates->min_date . " To: " . $dates->max_date . PHP_EOL;

echo PHP_EOL . "=== Sample order ===" . PHP_EOL;
$o = DB::table('orders')->select('id','status','channel_name','base_grand_total','customer_id','rating','table_number','created_at','updated_at')->first();
echo json_encode($o, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "=== Customers with orders ===" . PHP_EOL;
$cust = DB::table('orders')
    ->select('customer_id', DB::raw('count(*) as orders_count'), DB::raw('min(created_at) as first_order'), DB::raw('max(created_at) as last_order'))
    ->whereNotNull('customer_id')
    ->groupBy('customer_id')
    ->orderByDesc('orders_count')
    ->limit(10)
    ->get();
foreach ($cust as $c) echo "customer #{$c->customer_id}: {$c->orders_count} orders ({$c->first_order} - {$c->last_order})" . PHP_EOL;

echo PHP_EOL . "=== refunds table ===" . PHP_EOL;
try {
    echo "refunds: " . DB::table('refunds')->count() . " rows" . PHP_EOL;
} catch (\Exception $e) {
    echo "refunds: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== order_items sample ===" . PHP_EOL;
$item = DB::table('order_items')->select('id','name','sku','type','qty_ordered','base_price','base_total','product_id','additional','order_id')->first();
echo json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
