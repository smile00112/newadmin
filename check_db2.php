<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

echo "=== Payment methods ===" . PHP_EOL;
$payments = DB::table('order_payment')
    ->select('method', 'method_title', DB::raw('count(*) as cnt'))
    ->groupBy('method', 'method_title')
    ->get();
foreach ($payments as $p) echo $p->method . ' (' . $p->method_title . '): ' . $p->cnt . PHP_EOL;

echo PHP_EOL . "=== Sample order_payment additional ===" . PHP_EOL;
$sample = DB::table('order_payment')->select('method', 'method_title', 'additional')->limit(3)->get();
foreach ($sample as $s) echo $s->method . ': ' . mb_substr($s->additional, 0, 200) . PHP_EOL;

echo PHP_EOL . "=== Orders with status timeline ===" . PHP_EOL;
$orders = DB::table('orders')
    ->select('id', 'status', 'created_at', 'updated_at')
    ->whereIn('status', ['completed', 'preparing', 'ready'])
    ->limit(5)
    ->get();
foreach ($orders as $o) {
    $diff = strtotime($o->updated_at) - strtotime($o->created_at);
    echo "Order #{$o->id}: {$o->status} (created: {$o->created_at}, updated: {$o->updated_at}, diff: {$diff}s)" . PHP_EOL;
}

echo PHP_EOL . "=== order_items categories (product_id sample) ===" . PHP_EOL;
$items = DB::table('order_items')
    ->join('orders', 'orders.id', '=', 'order_items.order_id')
    ->select('order_items.name', 'order_items.product_id', 'order_items.base_price', 'orders.status')
    ->where('orders.status', '!=', 'canceled')
    ->groupBy('order_items.name', 'order_items.product_id', 'order_items.base_price', 'orders.status')
    ->limit(10)
    ->get();
foreach ($items as $i) echo "{$i->name} (product #{$i->product_id}, {$i->base_price}₽, status: {$i->status})" . PHP_EOL;

echo PHP_EOL . "=== product_categories table ===" . PHP_EOL;
try {
    $cats = DB::table('product_categories')
        ->join('category_translations', 'category_translations.category_id', '=', 'product_categories.category_id')
        ->where('category_translations.locale', 'ru')
        ->select('product_categories.category_id', 'category_translations.name', DB::raw('count(*) as products'))
        ->groupBy('product_categories.category_id', 'category_translations.name')
        ->get();
    foreach ($cats as $c) echo "Category #{$c->category_id}: {$c->name} ({$c->products} products)" . PHP_EOL;
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
