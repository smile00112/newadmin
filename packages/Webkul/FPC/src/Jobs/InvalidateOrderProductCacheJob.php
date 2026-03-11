<?php

namespace Webkul\FPC\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Product\Repositories\ProductBundleOptionProductRepository;
use Webkul\Product\Repositories\ProductGroupedProductRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Sales\Models\Order;

class InvalidateOrderProductCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        protected int $orderId
    ) {
        $this->onQueue('default');
    }

    public function handle(
        ProductRepository $productRepository,
        ProductBundleOptionProductRepository $bundleRepo,
        ProductGroupedProductRepository $groupedRepo
    ): void {
        $order = Order::with('all_items.product')->find($this->orderId);

        if (! $order) {
            return;
        }

        $urls = [];

        foreach ($order->all_items as $item) {
            if (! $item->product) {
                continue;
            }

            $product = $item->product;
            $products = [$product];

            if ($product->type === 'simple') {
                if ($product->parent_id) {
                    $products[] = $product->parent;
                }

                $bundleOptionProducts = $bundleRepo->findWhere(['product_id' => $product->id]);
                foreach ($bundleOptionProducts as $bop) {
                    $products[] = $bop->bundle_option->product;
                }

                $groupedOptionProducts = $groupedRepo->findWhere(['associated_product_id' => $product->id]);
                foreach ($groupedOptionProducts as $gop) {
                    $products[] = $gop->product;
                }
            }

            foreach ($products as $p) {
                if ($p && $p->url_key) {
                    $urls[] = '/'.$p->url_key;
                }
            }
        }

        if (! empty($urls)) {
            ResponseCache::forget(array_unique($urls));
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::warning('FPC: InvalidateOrderProductCacheJob failed', [
            'order_id' => $this->orderId,
            'message'  => $e->getMessage(),
        ]);
    }
}
