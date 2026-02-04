<?php

namespace Webkul\Notification\Listeners;

use Webkul\Notification\Events\ProductStatusChangedNotification;
use Webkul\Notification\Events\ProductPriceChangedNotification;
use Webkul\Product\Repositories\ProductRepository;

class Product
{
    /**
     * Store previous product state for comparison.
     *
     * @var array
     */
    protected static $previousProducts = [];

    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Store product state before update.
     *
     * @param  int  $productId
     * @return void
     */
    public function beforeUpdate($productId)
    {
        $product = $this->productRepository->find($productId);

        if ($product) {
            // Сохраняем предыдущее состояние товара
            static::$previousProducts[$productId] = [
                'price' => $product->price,
                'quantity' => $product->totalQuantity(),
                'manage_stock' => $product->manage_stock,
            ];
        }
    }

    /**
     * Check for product changes and send notifications.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    public function afterUpdate($product)
    {
        $productId = $product->id;

        // Проверяем, есть ли сохраненное предыдущее состояние
        if (! isset(static::$previousProducts[$productId])) {
            return;
        }

        $previous = static::$previousProducts[$productId];
        $current = [
            'price' => $product->price,
            'quantity' => $product->totalQuantity(),
            'manage_stock' => $product->manage_stock,
        ];

        // Проверяем изменение статуса (не в наличии)
        // Если было в наличии (quantity > 0), а стало нет (quantity <= 0) и управление складом включено
        if (
            $previous['manage_stock']
            && $previous['quantity'] > 0
            && $current['quantity'] <= 0
        ) {
            event(new ProductStatusChangedNotification([
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'status' => 'out_of_stock',
                'previous_qty' => $previous['quantity'],
                'current_qty' => $current['quantity'],
                'timestamp' => now()->toIso8601String(),
            ]));
        }

        // Проверяем изменение цены
        if ($previous['price'] != $current['price']) {
            event(new ProductPriceChangedNotification([
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'previous_price' => $previous['price'],
                'current_price' => $current['price'],
                'price_change' => $current['price'] - $previous['price'],
                'price_change_percent' => $previous['price'] > 0
                    ? round((($current['price'] - $previous['price']) / $previous['price']) * 100, 2)
                    : 0,
                'timestamp' => now()->toIso8601String(),
            ]));
        }

        // Удаляем сохраненное состояние после обработки
        unset(static::$previousProducts[$productId]);
    }
}
