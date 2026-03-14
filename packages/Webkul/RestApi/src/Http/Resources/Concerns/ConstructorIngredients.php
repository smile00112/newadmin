<?php

namespace Webkul\RestApi\Http\Resources\Concerns;

use Webkul\Product\Facades\ProductImage;
use Webkul\Product\Repositories\ProductRepository;

trait ConstructorIngredients
{
    /**
     * Сформировать информацию о выбранных ингредиентах конструктора из additional.
     *
     * Ожидает структуру additional['constructor_options'] в виде:
     *  { group_id: { product_id: qty, ... }, ... }.
     *
     * @return array<int, array{product_id: int, quantity: int, name: string, sku: string, base_image: array}>
     */
    protected function getIngredientsFromAdditional(array $additional): array
    {
        $constructorOptions = $additional['constructor_options'] ?? [];

        if (! is_array($constructorOptions) || empty($constructorOptions)) {
            return [];
        }

        $productQuantities = [];

        foreach ($constructorOptions as $groupId => $groupProducts) {
            if (! is_array($groupProducts)) {
                continue;
            }

            foreach ($groupProducts as $productId => $qty) {
                if (! $qty) {
                    continue;
                }

                $key = (int) $productId;

                if (! isset($productQuantities[$key])) {
                    $productQuantities[$key] = 0;
                }

                $productQuantities[$key] += (int) $qty;
            }
        }

        if (empty($productQuantities)) {
            return [];
        }

        $productIds = array_keys($productQuantities);

        /** @var ProductRepository $productRepository */
        $productRepository = app(ProductRepository::class);

        $products = $productRepository->findWhereIn('id', $productIds);

        $productsById = $products->keyBy('id');

        $result = [];

        foreach ($productQuantities as $productId => $qty) {
            $product = $productsById->get($productId);

            if (! $product) {
                continue;
            }

            $result[] = [
                'product_id' => (int) $productId,
                'quantity'   => (int) $qty,
                'name'       => (string) $product->name,
                'sku'        => (string) $product->sku,
                'base_image' => ProductImage::getProductBaseImage($product),
            ];
        }

        return $result;
    }
}
