<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductGroupedProductRepository;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;

class IikoNomenclatureImportService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected ProductGroupedProductRepository $productGroupedProductRepository,
        protected AttributeFamilyRepository $attributeFamilyRepository
    ) {}

    /**
     * Import nomenclature (categories and products) from iiko.
     *
     * @param  string  $organizationId
     * @param  array  $nomenclatureData
     * @param  array|null  $selectedGroupIds
     * @return array
     */
    public function importNomenclature(string $organizationId, array $nomenclatureData, ?array $selectedGroupIds = null): array
    {
        try {
            // Validate nomenclature data structure
            if (empty($nomenclatureData)) {
                throw new \Exception('Nomenclature data is empty');
            }

            DB::beginTransaction();

            $stats = [
                'categories_created' => 0,
                'categories_updated' => 0,
                'products_created' => 0,
                'products_updated' => 0,
                'grouped_products_created' => 0,
            ];

            // Extract categories and items from nomenclature data
            // Support both old format (groups/items) and new format (itemCategories with nested items)
            // New format is normalized in IikoNomenclatureService, but we handle both for safety
            $categories = $nomenclatureData['groups'] ?? $nomenclatureData['categories'] ?? [];
            $items = $nomenclatureData['items'] ?? $nomenclatureData['products'] ?? [];

            // If no groups/items found, try to extract from itemCategories (new API format)
            if (empty($categories) && isset($nomenclatureData['itemCategories']) && is_array($nomenclatureData['itemCategories'])) {
                foreach ($nomenclatureData['itemCategories'] as $category) {
                    $categories[] = [
                        'id' => $category['id'] ?? null,
                        'name' => $category['name'] ?? 'Unnamed Category',
                        'description' => $category['description'] ?? null,
                        'parentGroup' => null,
                    ];
                }
            }

            if (empty($items) && isset($nomenclatureData['itemCategories']) && is_array($nomenclatureData['itemCategories'])) {
                foreach ($nomenclatureData['itemCategories'] as $category) {
                    $categoryId = $category['id'] ?? null;
                    if (isset($category['items']) && is_array($category['items'])) {
                        foreach ($category['items'] as $item) {
                            $item['groupId'] = $categoryId;
                            $items[] = $item;
                        }
                    }
                }
            }

            // Filter by selected group IDs if provided
            if (!empty($selectedGroupIds) && is_array($selectedGroupIds)) {
                // Filter categories - only include groups with IDs in selectedGroupIds
                $categories = array_filter($categories, function ($category) use ($selectedGroupIds) {
                    $categoryId = $category['id'] ?? null;
                    return $categoryId && in_array($categoryId, $selectedGroupIds);
                });

                // Filter items - only include products with groupId in selectedGroupIds
                $items = array_filter($items, function ($item) use ($selectedGroupIds) {
                    $groupId = $item['groupId'] ?? null;
                    return $groupId && in_array($groupId, $selectedGroupIds);
                });
            }

            // Import categories first
            if (!empty($categories)) {
                try {
                    $categoryStats = $this->importCategories($categories);
                    $stats['categories_created'] = $categoryStats['created'];
                    $stats['categories_updated'] = $categoryStats['updated'];
                } catch (\Exception $e) {
                    Log::error('iiko: Error importing categories', [
                        'organization_id' => $organizationId,
                        'message' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }

            // Import products
            if (!empty($items)) {
                try {
                    $productStats = $this->importProducts($items);
                    $stats['products_created'] = $productStats['created'];
                    $stats['products_updated'] = $productStats['updated'];
                    $stats['grouped_products_created'] = $productStats['grouped_created'] ?? 0;
                } catch (\Exception $e) {
                    Log::error('iiko: Error importing products', [
                        'organization_id' => $organizationId,
                        'message' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }

            DB::commit();

            Log::info('iiko: Nomenclature imported successfully', [
                'organization_id' => $organizationId,
                'stats' => $stats,
            ]);

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Nomenclature imported successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('iiko: Exception importing nomenclature', [
                'organization_id' => $organizationId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to import nomenclature: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Import categories from iiko.
     *
     * @param  array  $categories
     * @return array
     */
    protected function importCategories(array $categories): array
    {
        $stats = ['created' => 0, 'updated' => 0];
        $categoryMap = []; // Map iiko category ID to our category ID

        // Sort categories by parentId to process parents first
        usort($categories, function ($a, $b) {
            $aParent = $a['parentGroup'] ?? null;
            $bParent = $b['parentGroup'] ?? null;

            if ($aParent === $bParent) {
                return 0;
            }

            if ($aParent === null) {
                return -1;
            }

            if ($bParent === null) {
                return 1;
            }

            return strcmp($aParent, $bParent);
        });

        foreach ($categories as $categoryData) {
            $iikoId = $categoryData['id'] ?? null;

            if (!$iikoId) {
                continue;
            }

            // Find existing category by iiko_id
            $existingCategory = $this->findCategoryByIikoId($iikoId);

            $categoryName = $categoryData['name'] ?? 'Unnamed Category';
            $parentIikoId = $categoryData['parentGroup'] ?? null;

            // Find parent category if exists
            $parentId = null;
            if ($parentIikoId && isset($categoryMap[$parentIikoId])) {
                $parentCategoryId = $categoryMap[$parentIikoId];
                // Ensure parentCategoryId is an integer and not null
                if (is_numeric($parentCategoryId) && $parentCategoryId > 0) {
                    $parentId = (int) $parentCategoryId;
                }
            }

            $categoryDataToSave = [
                'additional' => ['iiko_id' => $iikoId],
                'status' => 1,
            ];

            // Set parent_id if parent exists
            if ($parentId) {
                $categoryDataToSave['parent_id'] = $parentId;
            }

            // Set translations for all locales
            $locales = core()->getAllLocales();
            foreach ($locales as $locale) {
                $localeCode = is_string($locale->code) ? $locale->code : (string) $locale->code;
                $categoryDataToSave[$localeCode] = [
                    'name' => $categoryName,
                    'slug' => \Illuminate\Support\Str::slug($categoryName),
                    'description' => $categoryData['description'] ?? null,
                ];
            }

            if ($existingCategory) {
                // Update existing category
                Log::info([
                    'type' => 'update category',
                    '$categoryDataToSave' => $categoryDataToSave,
                    '$existingCategory->id' => $existingCategory->id,
                ]);
                $this->categoryRepository->update($categoryDataToSave, $existingCategory->id);
                $categoryId = $existingCategory->id;
                // Ensure categoryId is an integer before storing in map
                if (is_numeric($categoryId) && $categoryId > 0) {
                    $categoryMap[$iikoId] = (int) $categoryId;
                }
                $stats['updated']++;
            } else {
                // Create new category
                // Ensure status is integer, not boolean
                $categoryDataToSave['status'] = (int) ($categoryDataToSave['status'] ?? 1);
                
                $category = $this->categoryRepository->create($categoryDataToSave);
                $categoryId = $category->id;
                // Ensure categoryId is an integer before storing in map
                if (is_numeric($categoryId) && $categoryId > 0) {
                    $categoryMap[$iikoId] = (int) $categoryId;
                }
                $stats['created']++;
            }
        }

        return $stats;
    }

    /**
     * Import products from iiko.
     *
     * @param  array  $items
     * @return array
     */
    protected function importProducts(array $items): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'grouped_created' => 0];
        $categoryMap = $this->buildCategoryMap();

        foreach ($items as $item) {
            // Support both 'id' and 'itemId' from new API format
            $iikoId = $item['id'] ?? $item['itemId'] ?? null;

            if (!$iikoId) {
                continue;
            }

            $productType = $this->mapIikoProductType($item['type'] ?? 'DISH');

            // Extract prices from sizePrices or itemSizes (new API format)
            $prices = $item['sizePrices'] ?? [];
            if (empty($prices) && isset($item['itemSizes']) && is_array($item['itemSizes'])) {
                foreach ($item['itemSizes'] as $size) {
                    $sizePrice = 0;
                    if (isset($size['prices']) && is_array($size['prices']) && count($size['prices']) > 0) {
                        $sizePrice = $size['prices'][0]['price'] ?? 0;
                    }
                    $prices[] = [
                        'sizeId' => $size['sizeId'] ?? null,
                        'sizeName' => $size['sizeName'] ?? null,
                        'sizeCode' => $size['sizeCode'] ?? null,
                        'price' => $sizePrice,
                    ];
                }
            }

            // Handle products with multiple prices
            if (count($prices) > 1) {
                $result = $this->handleMultiPriceProduct($item, $categoryMap);
                if ($result) {
                    $stats['grouped_created']++;
                    $stats['created'] += $result['variants_created'] ?? 0;
                }
            } else {
                // Handle single price product
                $existingProduct = $this->findProductByIikoId($iikoId);

                if ($existingProduct) {
                    $this->updateProduct($existingProduct, $item, $productType, $categoryMap, $prices);
                    $stats['updated']++;
                } else {
                    $this->createProduct($item, $productType, $categoryMap, $prices);
                    $stats['created']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Map iiko product type to our product type.
     *
     * @param  string  $iikoType
     * @return string
     */
    protected function mapIikoProductType(string $iikoType): string
    {
        // Normalize to lowercase for comparison
        $normalizedType = strtolower(trim($iikoType));

        return match ($normalizedType) {
            'dish', 'product' => 'simple',
            'modifier' => 'ingredient',
            'group', 'grouped' => 'grouped',
            default => 'simple',
        };
    }

    /**
     * Handle product with multiple prices.
     *
     * @param  array  $item
     * @param  array  $categoryMap
     * @return array|null
     */
    protected function handleMultiPriceProduct(array $item, array $categoryMap): ?array
    {
        try {
            // Support both 'id' and 'itemId' from new API format
            $iikoId = $item['id'] ?? $item['itemId'] ?? null;
            $prices = $item['sizePrices'] ?? [];
            $categoryIikoId = $item['groupId'] ?? null;

            // Get or create category for price variants
            $priceVariantsCategory = $this->getOrCreatePriceVariantsCategory();

            // Get default attribute family
            $attributeFamily = $this->getDefaultAttributeFamily();

            $defaultChannelId = core()->getDefaultChannel()->id ?? null;
            $channels = [];
            if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
                $channels[] = (int) $defaultChannelId;
            }

            // Create main grouped product
            $mainProductData = [
                'type' => 'grouped',
                'sku' => 'iiko_' . $iikoId,
                'attribute_family_id' => $attributeFamily->id,
                'additional' => ['iiko_id' => $iikoId],
                'channels' => $channels,
            ];

            // Set translations
            $productName = $item['name'] ?? 'Unnamed Product';
            $locales = core()->getAllLocales();
            foreach ($locales as $locale) {
                $mainProductData[$locale->code] = [
                    'name' => $productName,
                    'short_description' => $item['description'] ?? null,
                    'description' => $item['description'] ?? null,
                ];
            }

            $mainProduct = $this->productRepository->create($mainProductData);

            // Set categories for main product
            $mainCategories = [];
            if ($categoryIikoId && isset($categoryMap[$categoryIikoId])) {
                $categoryId = $categoryMap[$categoryIikoId];
                // Ensure categoryId is an integer and not null
                if (is_numeric($categoryId) && $categoryId > 0) {
                    $mainCategories[] = (int) $categoryId;
                }
            }
            $mainProduct->categories()->sync($mainCategories);

            // Create price variant products
            $variantProducts = [];
            $variantsCreated = 0;

            foreach ($prices as $price) {
                $priceId = $price['sizeId'] ?? null;
                $priceValue = $price['price'] ?? 0;
                $priceName = $price['sizeName'] ?? '';

                $variantSku = 'iiko_' . $iikoId . '_price_' . $priceId;
                $variantName = $productName . ($priceName ? ' - ' . $priceName : '');

                $defaultChannelId = core()->getDefaultChannel()->id ?? null;
                $variantChannels = [];
                if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
                    $variantChannels[] = (int) $defaultChannelId;
                }

                $variantData = [
                    'type' => 'simple',
                    'sku' => $variantSku,
                    'attribute_family_id' => $attributeFamily->id,
                    'parent_id' => $mainProduct->id,
                    'additional' => [
                        'iiko_id' => $iikoId . '_price_' . $priceId,
                        'iiko_main_id' => $iikoId,
                    ],
                    'channels' => $variantChannels,
                ];

                // Set translations
                foreach ($locales as $locale) {
                    $variantData[$locale->code] = [
                        'name' => $variantName,
                        'short_description' => $item['description'] ?? null,
                        'description' => $item['description'] ?? null,
                        'price' => $priceValue,
                    ];
                }

                $variantProduct = $this->productRepository->create($variantData);

                // Set category for variant
                $priceVariantCategoryId = $priceVariantsCategory->id ?? null;
                $variantCategories = [];
                if ($priceVariantCategoryId && is_numeric($priceVariantCategoryId) && $priceVariantCategoryId > 0) {
                    $variantCategories[] = (int) $priceVariantCategoryId;
                }
                $variantProduct->categories()->sync($variantCategories);

                $variantProducts[] = $variantProduct;
                $variantsCreated++;
            }

            // Link variants to grouped product using saveGroupedProducts format
            $linksData = [];
            foreach ($variantProducts as $index => $variant) {
                $linksData['link_' . $variant->id] = [
                    'associated_product_id' => $variant->id,
                    'qty' => 1,
                    'sort_order' => $index,
                ];
            }

            $this->productGroupedProductRepository->saveGroupedProducts(
                ['links' => $linksData],
                $mainProduct
            );

            return [
                'variants_created' => $variantsCreated,
            ];
        } catch (\Exception $e) {
            Log::error('iiko: Error handling multi-price product', [
                'item_id' => $item['id'] ?? $item['itemId'] ?? null,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create a single product.
     *
     * @param  array  $item
     * @param  string  $productType
     * @param  array  $categoryMap
     * @param  array  $prices
     * @return void
     */
    protected function createProduct(array $item, string $productType, array $categoryMap, array $prices): void
    {
        // Support both 'id' and 'itemId' from new API format
        $iikoId = $item['id'] ?? $item['itemId'] ?? null;
        $attributeFamily = $this->getDefaultAttributeFamily();
        $price = !empty($prices) ? ($prices[0]['price'] ?? 0) : 0;

        $defaultChannelId = core()->getDefaultChannel()->id ?? null;
        $channels = [];
        if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
            $channels[] = (int) $defaultChannelId;
        }

            $productData = [
                'type' => $productType,
                'sku' => 'iiko_' . $iikoId,
                'attribute_family_id' => $attributeFamily->id,
                'additional' => ['iiko_id' => $iikoId],
                'channels' => $channels,
            ];

            // Set translations
            $productName = $item['name'] ?? 'Unnamed Product';
            $locales = core()->getAllLocales();
            foreach ($locales as $locale) {
                $productData[$locale->code] = [
                    'name' => $productName,
                    'short_description' => $item['description'] ?? null,
                    'description' => $item['description'] ?? null,
                    'price' => $price,
                ];
            }

            $product = $this->productRepository->create($productData);

            // Set categories
            $categoryIikoId = $item['groupId'] ?? null;
            $categories = [];
            if ($categoryIikoId && isset($categoryMap[$categoryIikoId])) {
                $categoryId = $categoryMap[$categoryIikoId];
                // Ensure categoryId is an integer and not null
                if (is_numeric($categoryId) && $categoryId > 0) {
                    $categories[] = (int) $categoryId;
                }
            }
            $product->categories()->sync($categories);
    }

    /**
     * Update existing product.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @param  array  $item
     * @param  string  $productType
     * @param  array  $categoryMap
     * @param  array  $prices
     * @return void
     */
    protected function updateProduct($product, array $item, string $productType, array $categoryMap, array $prices): void
    {
        $price = !empty($prices) ? ($prices[0]['price'] ?? 0) : 0;
        $productName = $item['name'] ?? 'Unnamed Product';

        // Support both 'id' and 'itemId' from new API format
        $itemId = $item['id'] ?? $item['itemId'] ?? null;
        $defaultChannelId = core()->getDefaultChannel()->id ?? null;
        $channels = [];
        if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
            $channels[] = (int) $defaultChannelId;
        }

        $productData = [
            'type' => $productType,
            'additional' => array_merge($product->additional ?? [], ['iiko_id' => $itemId]),
            'channels' => $channels,
        ];

        // Set translations
        $locales = core()->getAllLocales();
        foreach ($locales as $locale) {
            $productData[$locale->code] = [
                'name' => $productName,
                'short_description' => $item['description'] ?? null,
                'description' => $item['description'] ?? null,
                'price' => $price,
            ];
        }

        // Set categories
        $categoryIikoId = $item['groupId'] ?? null;
        $categories = [];
        if ($categoryIikoId && isset($categoryMap[$categoryIikoId])) {
            $categoryId = $categoryMap[$categoryIikoId];
            // Ensure categoryId is an integer and not null
            if (is_numeric($categoryId) && $categoryId > 0) {
                $categories[] = (int) $categoryId;
            }
        }
        $productData['categories'] = $categories;

        $this->productRepository->update($productData, $product->id);
    }

    /**
     * Find category by iiko_id.
     *
     * @param  string  $iikoId
     * @return \Webkul\Category\Contracts\Category|null
     */
    protected function findCategoryByIikoId(string $iikoId)
    {
        return $this->categoryRepository
            ->getModel()
            ->whereJsonContains('additional->iiko_id', $iikoId)
            ->first();
    }

    /**
     * Find product by iiko_id.
     *
     * @param  string  $iikoId
     * @return \Webkul\Product\Contracts\Product|null
     */
    protected function findProductByIikoId(string $iikoId)
    {
        return $this->productRepository
            ->getModel()
            ->whereJsonContains('additional->iiko_id', $iikoId)
            ->first();
    }

    /**
     * Build category map (iiko_id => category_id).
     *
     * @return array
     */
    protected function buildCategoryMap(): array
    {
        $categories = $this->categoryRepository
            ->getModel()
            ->whereNotNull('additional')
            ->get();

        $map = [];
        foreach ($categories as $category) {
            $iikoId = $category->additional['iiko_id'] ?? null;
            $categoryId = $category->id ?? null;
            // Ensure both iikoId and categoryId are valid
            if ($iikoId && $categoryId && is_numeric($categoryId) && $categoryId > 0) {
                $map[$iikoId] = (int) $categoryId;
            }
        }

        return $map;
    }

    /**
     * Get or create category for price variants.
     *
     * @return \Webkul\Category\Contracts\Category
     */
    protected function getOrCreatePriceVariantsCategory()
    {
        $categoryName = 'Варианты цен iiko';

        // Try to find existing category
        $existing = $this->categoryRepository
            ->getModel()
            ->whereHas('translations', function ($query) use ($categoryName) {
                $query->where('name', $categoryName);
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        // Create new category
        $locales = core()->getAllLocales();
        $categoryData = [
            'status' => 1,
            'additional' => ['iiko_price_variants' => true],
        ];

        foreach ($locales as $locale) {
            $localeCode = is_string($locale->code) ? $locale->code : (string) $locale->code;
            $categoryData[$localeCode] = [
                'name' => $categoryName,
                'slug' => \Illuminate\Support\Str::slug($categoryName),
            ];
        }

        return $this->categoryRepository->create($categoryData);
    }

    /**
     * Get default attribute family.
     *
     * @return \Webkul\Attribute\Contracts\AttributeFamily
     */
    protected function getDefaultAttributeFamily()
    {
        $family = $this->attributeFamilyRepository->findWhere(['code' => 'default'])->first();

        if (!$family) {
            $family = $this->attributeFamilyRepository->first();
        }

        if (!$family) {
            throw new \Exception('No attribute family found');
        }

        return $family;
    }
}
