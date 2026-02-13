<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductGroupedProductRepository;
use Webkul\Product\Repositories\ProductConstructorRepository;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Product\Helpers\Indexers\Flat as FlatIndexer;

class IikoNomenclatureImportService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected ProductGroupedProductRepository $productGroupedProductRepository,
        protected ProductConstructorRepository $productConstructorRepository,
        protected ProductImageRepository $productImageRepository,
        protected AttributeFamilyRepository $attributeFamilyRepository,
        protected FlatIndexer $flatIndexer
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
                'constructor_products_created' => 0,
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
                    $stats['constructor_products_created'] = $productStats['constructor_created'] ?? 0;
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
        $stats = ['created' => 0, 'updated' => 0, 'grouped_created' => 0, 'constructor_created' => 0];
        $categoryMap = $this->buildCategoryMap();

        foreach ($items as $item) {
            // Support both 'id' and 'itemId' from new API format
            $iikoId = $item['id'] ?? $item['itemId'] ?? null;

            if (!$iikoId) {
                continue;
            }

            // Handle products with itemModifierGroups (constructor products) - highest priority
            if ($this->hasItemModifierGroups($item)) {
                $result = $this->handleConstructorProduct($item, $categoryMap);
                if ($result) {
                    $stats['constructor_created']++;
                    if ($result === 'created') {
                        $stats['created']++;
                    } else {
                        $stats['updated']++;
                    }
                }
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
                $sku = 'iiko_' . $iikoId;
                $existingProduct = $this->findProductByIikoId($iikoId);

                // If not found by iiko_id, check by SKU
                if (!$existingProduct) {
                    $existingProduct = $this->findProductBySku($sku);
                }

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
     * Check if item has itemModifierGroups (for constructor product).
     *
     * @param  array  $item
     * @return bool
     */
    protected function hasItemModifierGroups(array $item): bool
    {
        if (isset($item['itemModifierGroups']) && is_array($item['itemModifierGroups']) && count($item['itemModifierGroups']) > 0) {
            return true;
        }
        if (isset($item['itemSizes']) && is_array($item['itemSizes'])) {
            foreach ($item['itemSizes'] as $size) {
                if (isset($size['itemModifierGroups']) && is_array($size['itemModifierGroups']) && count($size['itemModifierGroups']) > 0) {
                    return true;
                }
            }
        }
        return false;
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

            // Check if main grouped product already exists by SKU
            $mainSku = 'iiko_' . $iikoId;
            $mainProduct = $this->findProductBySku($mainSku);

            if (!$mainProduct) {
                // Create main grouped product
                $mainProductData = [
                    'type' => 'grouped',
                    'sku' => $mainSku,
                    'attribute_family_id' => $attributeFamily->id,
                    'additional' => ['iiko_id' => $iikoId],
                    'channels' => $channels,
                    'status' => 1,
                    'visible_individually' => 1,
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

                // Refresh product_flat index for main product
                $mainProduct->refresh();
                $this->flatIndexer->refresh($mainProduct);
            } else {
                $productName = $item['name'] ?? 'Unnamed Product';
            }

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

                // Check if variant already exists by SKU
                $variantProduct = $this->findProductBySku($variantSku);

                if (!$variantProduct) {
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
                        'status' => 1,
                        'visible_individually' => 0,
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

                    // Refresh product_flat index for variant
                    $variantProduct->refresh();
                    $this->flatIndexer->refresh($variantProduct);

                    // Set category for variant
                    $priceVariantCategoryId = $priceVariantsCategory->id ?? null;
                    $variantCategories = [];
                    if ($priceVariantCategoryId && is_numeric($priceVariantCategoryId) && $priceVariantCategoryId > 0) {
                        $variantCategories[] = (int) $priceVariantCategoryId;
                    }
                    $variantProduct->categories()->sync($variantCategories);

                    $variantsCreated++;
                }

                $variantProducts[] = $variantProduct;
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

            // Refresh main product again after linking variants
            $mainProduct->refresh();
            $this->flatIndexer->refresh($mainProduct);

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
     * Handle product with itemModifierGroups (constructor product).
     *
     * @param  array  $item
     * @param  array  $categoryMap
     * @return string|null 'created'|'updated' on success, null on failure
     */
    protected function handleConstructorProduct(array $item, array $categoryMap): ?string
    {
        try {
            $iikoId = $item['id'] ?? $item['itemId'] ?? null;
            if (!$iikoId) {
                return null;
            }

            $modifierGroups = $this->extractItemModifierGroups($item);
            if (empty($modifierGroups)) {
                return null;
            }

            $sku = 'iiko_' . $iikoId;
            $existingProduct = $this->findProductByIikoId($iikoId);
            if (!$existingProduct) {
                $existingProduct = $this->findProductBySku($sku);
            }

            $attributeFamily = $this->getDefaultAttributeFamily();
            $defaultChannelId = core()->getDefaultChannel()->id ?? null;
            $channels = [];
            if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
                $channels[] = (int) $defaultChannelId;
            }

            $productName = $item['name'] ?? 'Unnamed Product';
            $price = 0;
            if (!empty($item['sizePrices'])) {
                $price = $item['sizePrices'][0]['price'] ?? 0;
            } elseif (!empty($item['price'])) {
                $price = $item['price'];
            }

            if ($existingProduct) {
                $productData = [
                    'type' => 'constructor',
                    'additional' => array_merge($existingProduct->additional ?? [], ['iiko_id' => $iikoId]),
                    'channels' => $channels,
                ];
                $locales = core()->getAllLocales();
                foreach ($locales as $locale) {
                    $productData[$locale->code] = [
                        'name' => $productName,
                        'short_description' => $item['description'] ?? null,
                        'description' => $item['description'] ?? null,
                        'price' => $price,
                    ];
                }

                Log::info('iiko: edit product data', [
                    '$productData' => $productData,

                ]);

                $categoryIikoId = $item['groupId'] ?? null;
                $categories = [];
                if ($categoryIikoId && isset($categoryMap[$categoryIikoId]) && is_numeric($categoryMap[$categoryIikoId]) && $categoryMap[$categoryIikoId] > 0) {
                    $categories[] = (int) $categoryMap[$categoryIikoId];
                }
                $productData['categories'] = $categories;
                $this->productRepository->update($productData, $existingProduct->id);
                $product = $existingProduct->refresh();
                $result = 'updated';
            } else {
                $productData = [
                    'type' => 'constructor',
                    'sku' => $sku,
                    'attribute_family_id' => $attributeFamily->id,
                    'additional' => ['iiko_id' => $iikoId],
                    'channels' => $channels,
                    'status' => 1,
                    'visible_individually' => 1,
                ];
                $locales = core()->getAllLocales();
                foreach ($locales as $locale) {
                    $productData[$locale->code] = [
                        'name' => $productName,
                        'short_description' => $item['description'] ?? null,
                        'description' => $item['description'] ?? null,
                        'price' => $price,
                    ];
                }

                Log::info('iiko: Create product data', [
                    '$productData' => $productData,

                ]);



                $product = $this->productRepository->create($productData);
                $categoryIikoId = $item['groupId'] ?? null;
                $categories = [];
                if ($categoryIikoId && isset($categoryMap[$categoryIikoId]) && is_numeric($categoryMap[$categoryIikoId]) && $categoryMap[$categoryIikoId] > 0) {
                    $categories[] = (int) $categoryMap[$categoryIikoId];
                }
                $product->categories()->sync($categories);
                $product->refresh();
                $result = 'created';
            }

            $constructorData = $this->processModifierGroups($modifierGroups, $product->id);
            if (!empty($constructorData)) {
                $this->productConstructorRepository->saveConstructor($constructorData, $product);
            }

            $this->flatIndexer->refresh($product);
            return $result;
        } catch (\Exception $e) {
            Log::error('iiko: Error handling constructor product', [
                'item_id' => $item['id'] ?? $item['itemId'] ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Extract itemModifierGroups from item (from item or from first itemSize).
     *
     * @param  array  $item
     * @return array
     */
    protected function extractItemModifierGroups(array $item): array
    {
        if (isset($item['itemModifierGroups']) && is_array($item['itemModifierGroups']) && count($item['itemModifierGroups']) > 0) {
            return $item['itemModifierGroups'];
        }
        if (isset($item['itemSizes']) && is_array($item['itemSizes']) && count($item['itemSizes']) > 0) {
            $firstSize = $item['itemSizes'][0];
            if (isset($firstSize['itemModifierGroups']) && is_array($firstSize['itemModifierGroups'])) {
                return $firstSize['itemModifierGroups'];
            }
        }
        return [];
    }

    /**
     * Process modifier groups and build constructor data for saveConstructor.
     *
     * @param  array  $modifierGroups
     * @param  int  $parentProductId
     * @return array
     */
    protected function processModifierGroups(array $modifierGroups, int $parentProductId): array
    {
        $constructorGroups = [];
        $sortOrder = 0;

        foreach ($modifierGroups as $modGroup) {
            $items = $modGroup['items'] ?? [];
            if (empty($items)) {
                continue;
            }

            $restrictions = $modGroup['restrictions'] ?? [];
            $minQty = (int) ($restrictions['minQuantity'] ?? 0);
            $maxQty = (int) ($restrictions['maxQuantity'] ?? 0);
            $byDefault = (int) ($restrictions['byDefault'] ?? 0);
            $hideIfDefault = (bool) ($restrictions['hideIfDefaultQuantity'] ?? false);

            $maxQtySingle = 1;
            foreach ($items as $modItem) {
                $itemRestrictions = $modItem['restrictions'] ?? [];
                $itemMax = (int) ($itemRestrictions['maxQuantity'] ?? 1);
                if ($itemMax > $maxQtySingle) {
                    $maxQtySingle = $itemMax;
                }
            }

            $fieldType = $maxQtySingle > 1 ? 'checkbox' : 'radio';
            $checkedType = $maxQty > 1 ? 'multiple' : 'once';
            $required = $minQty > 0 || $byDefault > 0;

            $groupProducts = [];
            $productSort = 0;
            foreach ($items as $modItem) {
                $ingredientProduct = $this->findOrCreateIngredient($modItem);
                if (!$ingredientProduct) {
                    continue;
                }
                $itemRestrictions = $modItem['restrictions'] ?? [];
                $byDefaultItem = (int) ($itemRestrictions['byDefault'] ?? 0);
                $position = (int) ($modItem['position'] ?? $productSort);
                $groupProducts[] = [
                    'id' => $ingredientProduct->id,
                    'sort' => $position,
                    'default' => $byDefaultItem > 0,
                ];
                $productSort++;
            }

            if (empty($groupProducts)) {
                continue;
            }

            $constructorGroups[] = [
                'name' => $modGroup['name'] ?? 'Unnamed Group',
                'field_type' => $fieldType,
                'checked_type' => $checkedType,
                'quantity_min' => $minQty,
                'quantity_max' => $maxQty,
                'show_title' => true,
                'opened_by_default' => !$hideIfDefault,
                'zero_price' => false,
                'required' => $required,
                'hidden' => (bool) ($modGroup['isHidden'] ?? false),
                'sort' => $sortOrder,
                'products' => $groupProducts,
            ];
            $sortOrder++;
        }

        if (empty($constructorGroups)) {
            return [];
        }

        return [
            'constructor' => [
                [
                    'visible' => true,
                    'required' => false,
                    'combo' => false,
                    'discount' => false,
                    'design' => 'category',
                    'groups' => $constructorGroups,
                ],
            ],
        ];
    }

    /**
     * Download image from URL and save it for product.
     *
     * @param  string  $imageUrl
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return bool
     */
    protected function downloadAndSaveImage(string $imageUrl, $product): bool
    {
        try {
            if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                return false;
            }

            // Download image
            $response = Http::timeout(10)->get($imageUrl);

            if (!$response->successful()) {
                Log::warning('iiko: Failed to download image', [
                    'url' => $imageUrl,
                    'status' => $response->status(),
                ]);
                return false;
            }

            $imageContent = $response->body();
            if (empty($imageContent)) {
                return false;
            }

            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'iiko_image_');
            if ($tempFile === false) {
                return false;
            }

            file_put_contents($tempFile, $imageContent);

            // Create UploadedFile instance
            $uploadedFile = new UploadedFile(
                $tempFile,
                basename($imageUrl),
                mime_content_type($tempFile) ?: 'image/jpeg',
                null,
                true
            );

            // Process image with ImageManager
            $imageManager = new ImageManager();
            $image = $imageManager->make($uploadedFile)->encode('webp');

            // Get product directory
            $imageDirectory = $this->productImageRepository->getProductDirectory($product);
            $path = $imageDirectory . '/' . Str::random(40) . '.webp';

            // Save image
            Storage::put($path, $image);

            // Get next position for the image
            $maxPosition = $this->productImageRepository
                ->where('product_id', $product->id)
                ->where('type', 'images')
                ->max('position') ?? 0;
            $nextPosition = $maxPosition + 1;

            // Create product image record
            $this->productImageRepository->create([
                'type' => 'images',
                'path' => $path,
                'product_id' => $product->id,
                'position' => $nextPosition,
            ]);

            // Clean up temporary file
            @unlink($tempFile);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Error downloading and saving image', [
                'url' => $imageUrl,
                'product_id' => $product->id ?? null,
                'message' => $e->getMessage(),
            ]);

            // Clean up temporary file if exists
            if (isset($tempFile) && file_exists($tempFile)) {
                @unlink($tempFile);
            }

            return false;
        }
    }

    /**
     * Find or create ingredient product from modifier item.
     *
     * @param  array  $modItem
     * @return \Webkul\Product\Contracts\Product|null
     */
    protected function findOrCreateIngredient(array $modItem)
    {
        $itemId = $modItem['itemId'] ?? null;
        $sku = $modItem['sku'] ?? null;
        $name = $modItem['name'] ?? 'Unnamed Ingredient';
        $description = $modItem['description'] ?? null;
        $price = 0;
        if (isset($modItem['prices']) && is_array($modItem['prices']) && count($modItem['prices']) > 0) {
            $price = (float) ($modItem['prices'][0]['price'] ?? 0);
        }
        $buttonImageUrl = $modItem['buttonImageUrl'] ?? null;

        $ingredientSku = $itemId ? 'iiko_' . $itemId : ($sku ? 'iiko_' . $sku : null);
        if (!$ingredientSku) {
            return null;
        }

        $existing = null;
        if ($itemId) {
            $existing = $this->findProductByIikoId($itemId);
        }
        if (!$existing) {
            $existing = $this->findProductBySku($ingredientSku);
        }

        // Update existing ingredient
        if ($existing) {
            $defaultChannelId = core()->getDefaultChannel()->id ?? null;
            $channels = [];
            if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
                $channels[] = (int) $defaultChannelId;
            }

            $productData = [
                'additional' => array_merge($existing->additional ?? [], ['iiko_id' => $itemId ?? $sku]),
                'channels' => $channels,
            ];

            $locales = core()->getAllLocales();
            foreach ($locales as $locale) {
                $productData[$locale->code] = [
                    'name' => $name,
                    'short_description' => $description,
                    'description' => $description,
                    'price' => $price,
                ];
            }

            $this->productRepository->update($productData, $existing->id);
            $existing->refresh();

            // Handle image if buttonImageUrl is provided
            if (!empty($buttonImageUrl)) {
                $this->downloadAndSaveImage($buttonImageUrl, $existing);
            }

            $this->flatIndexer->refresh($existing);
            return $existing;
        }

        // Create new ingredient - double check before creating to handle race conditions
        // Check again in case product was created between first check and now
        $existing = $this->findProductBySku($ingredientSku);
        if ($existing) {
            // Product was created by another process, update it instead
            $defaultChannelId = core()->getDefaultChannel()->id ?? null;
            $channels = [];
            if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
                $channels[] = (int) $defaultChannelId;
            }

            $productData = [
                'additional' => array_merge($existing->additional ?? [], ['iiko_id' => $itemId ?? $sku]),
                'channels' => $channels,
            ];

            $locales = core()->getAllLocales();
            foreach ($locales as $locale) {
                $productData[$locale->code] = [
                    'name' => $name,
                    'short_description' => $description,
                    'description' => $description,
                    'price' => $price,
                ];
            }

            $this->productRepository->update($productData, $existing->id);
            $existing->refresh();

            // Handle image if buttonImageUrl is provided
            if (!empty($buttonImageUrl)) {
                $this->downloadAndSaveImage($buttonImageUrl, $existing);
            }

            $this->flatIndexer->refresh($existing);
            return $existing;
        }

        $attributeFamily = $this->getDefaultAttributeFamily();
        $defaultChannelId = core()->getDefaultChannel()->id ?? null;
        $channels = [];
        if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
            $channels[] = (int) $defaultChannelId;
        }

        $productData = [
            'type' => 'ingredient',
            'sku' => $ingredientSku,
            'attribute_family_id' => $attributeFamily->id,
            'additional' => ['iiko_id' => $itemId ?? $sku],
            'channels' => $channels,
            'status' => 1,
            'visible_individually' => 0,
        ];
        $locales = core()->getAllLocales();
        foreach ($locales as $locale) {
            $productData[$locale->code] = [
                'name' => $name,
                'short_description' => $description,
                'description' => $description,
                'price' => $price,
            ];
        }

        try {
            $product = $this->productRepository->create($productData);
            $product->refresh();
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate key error (1062)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                // Product was created by another process, find and update it
                $existing = $this->findProductBySku($ingredientSku);
                if ($existing) {
                    $defaultChannelId = core()->getDefaultChannel()->id ?? null;
                    $channels = [];
                    if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
                        $channels[] = (int) $defaultChannelId;
                    }

                    $productData = [
                        'additional' => array_merge($existing->additional ?? [], ['iiko_id' => $itemId ?? $sku]),
                        'channels' => $channels,
                    ];

                    $locales = core()->getAllLocales();
                    foreach ($locales as $locale) {
                        $productData[$locale->code] = [
                            'name' => $name,
                            'short_description' => $description,
                            'description' => $description,
                            'price' => $price,
                        ];
                    }

                    $this->productRepository->update($productData, $existing->id);
                    $existing->refresh();

                    // Handle image if buttonImageUrl is provided
                    if (!empty($buttonImageUrl)) {
                        $this->downloadAndSaveImage($buttonImageUrl, $existing);
                    }

                    $this->flatIndexer->refresh($existing);
                    return $existing;
                }
            }
            // Re-throw if it's not a duplicate key error
            throw $e;
        }

        // Handle image if buttonImageUrl is provided
        if (!empty($buttonImageUrl)) {
            $this->downloadAndSaveImage($buttonImageUrl, $product);
        }

        $this->flatIndexer->refresh($product);
        return $product;
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
        $sku = 'iiko_' . $iikoId;

        // Check if product already exists by SKU
        $existingProduct = $this->findProductBySku($sku);
        if ($existingProduct) {
            // Update existing product instead of creating new one
            $this->updateProduct($existingProduct, $item, $productType, $categoryMap, $prices);
            return;
        }

        $attributeFamily = $this->getDefaultAttributeFamily();
        $price = !empty($prices) ? ($prices[0]['price'] ?? 0) : 0;

        $defaultChannelId = core()->getDefaultChannel()->id ?? null;
        $channels = [];
        if ($defaultChannelId && is_numeric($defaultChannelId) && $defaultChannelId > 0) {
            $channels[] = (int) $defaultChannelId;
        }

        $productData = [
            'type' => $productType,
            'sku' => $sku,
            'attribute_family_id' => $attributeFamily->id,
            'additional' => ['iiko_id' => $iikoId],
            'channels' => $channels,
            'status' => 1,
            'visible_individually' => 1,
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

        // Refresh product_flat index after creating product and setting categories
        $product->refresh();
        $this->flatIndexer->refresh($product);
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

        // Refresh product_flat index after updating product
        $product->refresh();
        $this->flatIndexer->refresh($product);
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
     * Find product by SKU.
     *
     * @param  string  $sku
     * @return \Webkul\Product\Contracts\Product|null
     */
    protected function findProductBySku(string $sku)
    {
        return $this->productRepository
            ->findOneByField('sku', $sku);
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
