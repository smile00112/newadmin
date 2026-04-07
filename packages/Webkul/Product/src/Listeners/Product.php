<?php

namespace Webkul\Product\Listeners;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Jobs\ElasticSearch\DeleteIndex as DeleteElasticSearchIndexJob;
use Webkul\Product\Jobs\ElasticSearch\UpdateCreateIndex as UpdateCreateElasticSearchIndexJob;
use Webkul\Product\Jobs\UpdateProductFlatIndex as UpdateProductFlatIndexJob;
use Webkul\Product\Jobs\UpdateCreateInventoryIndex as UpdateCreateInventoryIndexJob;
use Webkul\Product\Jobs\UpdateCreatePriceIndex as UpdateCreatePriceIndexJob;
use Webkul\Product\Repositories\ProductBundleOptionProductRepository;
use Webkul\Product\Repositories\ProductGroupedProductRepository;
use Webkul\Product\Repositories\ProductRepository;

class Product
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductBundleOptionProductRepository $productBundleOptionProductRepository,
        protected ProductGroupedProductRepository $productGroupedProductRepository
    ) {}

    /**
     * Проверяем, используется ли ElasticSearch.
     */
    protected function isElasticEnabled(): bool
    {
        static $enabled = null;

        if ($enabled === null) {
            try {
                $enabled = core()->getConfigData('catalog.products.search.engine') === 'elastic';
            } catch (\Throwable $e) {
                $enabled = false;
            }
        }

        return $enabled;
    }

    /**
     * Принудительно закрыть HTTP-соединение для сред без fastcgi_finish_request
     * (например, php artisan serve).
     */
    protected function flushConnection(): void
    {
        if (function_exists('fastcgi_finish_request')) {
            // Уже вызвано в index.php, но на случай если нет
            fastcgi_finish_request();
            return;
        }

        // Для встроенного PHP-сервера (artisan serve)
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();

        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }
    }

    /**
     * After product create: refresh flat index, ES only if configured.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    public function afterCreate($product)
    {
        $productId = $product->id;
        $productIds = $this->getAllRelatedProductIds($product);
        $isElastic = $this->isElasticEnabled();

        if (config('queue.default') === 'sync') {
            app()->terminating(function () use ($productId, $productIds, $isElastic) {
                $this->flushConnection();

                UpdateProductFlatIndexJob::dispatchSync($productId);

                if ($isElastic) {
                    UpdateCreateElasticSearchIndexJob::dispatchSync($productIds);
                }
            });
        } else {
            UpdateProductFlatIndexJob::dispatch($productId);

            if ($isElastic) {
                UpdateCreateElasticSearchIndexJob::dispatch($productIds);
            }
        }
    }

    /**
     * After product update: refresh flat, inventory, price, ES indices.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    public function afterUpdate($product)
    {
        $productId = $product->id;
        $productIds = $this->getAllRelatedProductIds($product);
        $isElastic = $this->isElasticEnabled();

        if (config('queue.default') === 'sync') {
            app()->terminating(function () use ($productId, $productIds, $isElastic) {
                $this->flushConnection();

                UpdateProductFlatIndexJob::dispatchSync($productId);
                // Запускаем inventory и price параллельно (не chained)
                (new UpdateCreateInventoryIndexJob($productIds))->handle();
                (new UpdateCreatePriceIndexJob($productIds))->handle();

                if ($isElastic) {
                    (new UpdateCreateElasticSearchIndexJob($productIds))->handle();
                }
            });
        } else {
            UpdateProductFlatIndexJob::dispatch($productId);

            $chain = [
                new UpdateCreateInventoryIndexJob($productIds),
                new UpdateCreatePriceIndexJob($productIds),
            ];

            if ($isElastic) {
                $chain[] = new UpdateCreateElasticSearchIndexJob($productIds);
            }

            Bus::chain($chain)->dispatch();
        }
    }

    /**
     * Delete product indices
     *
     * @param  int  $productId
     * @return void
     */
    public function beforeDelete($productId)
    {
        if (core()->getConfigData('catalog.products.search.engine') != 'elastic') {
            return;
        }

        $product = $this->productRepository->find($productId);

        if (! $product) {
            return;
        }

        $productIds = $this->getAllRelatedProductIds($product);

        DeleteElasticSearchIndexJob::dispatch($productIds);
    }

    /**
     * Returns parents bundle product ids associated with simple product
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return array
     */
    public function getAllRelatedProductIds($product)
    {
        $productIds = [$product->id];

        if ($product->type == 'simple') {
            if ($product->parent_id) {
                $productIds[] = $product->parent_id;
            }

            $productIds = array_merge(
                $productIds,
                $this->getParentBundleProductIds($product),
                $this->getParentGroupProductIds($product)
            );
        } elseif ($product->type == 'configurable') {
            $productIds = [
                ...$product->variants->pluck('id')->toArray(),
                ...$productIds,
            ];
        }

        return $productIds;
    }

    /**
     * Returns parents bundle product ids associated with simple product
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return array
     */
    public function getParentBundleProductIds($product)
    {
        $bundleOptionProducts = $this->productBundleOptionProductRepository->findWhere([
            'product_id' => $product->id,
        ]);

        $productIds = [];

        foreach ($bundleOptionProducts as $bundleOptionProduct) {
            $productIds[] = $bundleOptionProduct->bundle_option->product_id;
        }

        return $productIds;
    }

    /**
     * Returns parents group product ids associated with simple product
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return array
     */
    public function getParentGroupProductIds($product)
    {
        $groupedOptionProducts = $this->productGroupedProductRepository->findWhere([
            'associated_product_id' => $product->id,
        ]);

        return $groupedOptionProducts->pluck('product_id')->toArray();
    }
}
