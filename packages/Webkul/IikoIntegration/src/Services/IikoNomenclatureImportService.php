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
use Webkul\Product\Repositories\ProductBundleOptionRepository;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Product\Helpers\Indexers\Flat as FlatIndexer;
use Webkul\IikoIntegration\Repositories\IikoSettingRepository;
use Webkul\IikoIntegration\Models\IikoSetting;

class IikoNomenclatureImportService
{
    /**
     * Create a new service instance.
     */
    private array $productsToRefresh = [];

    private array $imagesToDownload = [];

    private mixed $cachedAttributeFamily = null;

    private ?array $cachedLocales = null;

    private ?string $cachedChannelCode = null;

    private ?int $cachedChannelId = null;

    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected ProductGroupedProductRepository $productGroupedProductRepository,
        protected ProductConstructorRepository $productConstructorRepository,
        protected ProductBundleOptionRepository $productBundleOptionRepository,
        protected ProductImageRepository $productImageRepository,
        protected ProductAttributeValueRepository $productAttributeValueRepository,
        protected ProductInventoryRepository $productInventoryRepository,
        protected AttributeFamilyRepository $attributeFamilyRepository,
        protected FlatIndexer $flatIndexer,
        protected IikoSettingRepository $settingRepository
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
            $this->productsToRefresh = [];
            $this->imagesToDownload = [];

            Log::debug('iiko[service]: STEP A — importNomenclature start', [
                'organization_id' => $organizationId,
                'nomenclature_keys' => array_keys($nomenclatureData),
                'selected_group_ids' => $selectedGroupIds,
            ]);

            // Validate nomenclature data structure
            if (empty($nomenclatureData)) {
                throw new \Exception('Nomenclature data is empty');
            }

            DB::beginTransaction();
            Log::debug('iiko[service]: STEP B — DB transaction started');

            $stats = [
                'categories_created' => 0,
                'categories_updated' => 0,
                'products_created' => 0,
                'products_updated' => 0,
                'grouped_products_created' => 0,
                'constructor_products_created' => 0,
                'configurable_constructor_products_created' => 0,
                'combo_products_created' => 0,
            ];

            // Extract categories and items from nomenclature data
            // Support both old format (groups/items) and new format (itemCategories with nested items)
            // New format is normalized in IikoNomenclatureService, but we handle both for safety
            $categories = $nomenclatureData['groups'] ?? $nomenclatureData['categories'] ?? [];
            $items = $nomenclatureData['items'] ?? $nomenclatureData['products'] ?? [];
            Log::debug('iiko[service]: STEP C — raw extract', [
                'categories_count' => count($categories),
                'items_count' => count($items),
            ]);

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

            Log::debug('iiko[service]: STEP D — after itemCategories fallback', [
                'categories_count' => count($categories),
                'items_count' => count($items),
            ]);

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

            Log::debug('iiko[service]: STEP E — after filtering by groupIds', [
                'categories_count' => count($categories),
                'items_count' => count($items),
            ]);

            // Import categories first
            if (!empty($categories)) {
                Log::debug('iiko[service]: STEP F — importing categories', ['count' => count($categories)]);
                try {
                    $categoryStats = $this->importCategories($categories);
                    $stats['categories_created'] = $categoryStats['created'];
                    $stats['categories_updated'] = $categoryStats['updated'];
                    Log::debug('iiko[service]: STEP G — categories imported', $categoryStats);
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
                Log::debug('iiko[service]: STEP H — importing products', ['count' => count($items)]);
                try {
                    $productStats = $this->importProducts($items);
                    $stats['products_created'] = $productStats['created'];
                    $stats['products_updated'] = $productStats['updated'];
                    $stats['grouped_products_created'] = $productStats['grouped_created'] ?? 0;
                    $stats['constructor_products_created'] = $productStats['constructor_created'] ?? 0;
                    $stats['configurable_constructor_products_created'] = $productStats['configurable_constructor_created'] ?? 0;
                    $stats['combo_products_created'] = $productStats['combo_created'] ?? 0;
                    Log::debug('iiko[service]: STEP I — products imported', $productStats);
                } catch (\Exception $e) {
                    Log::error('iiko: Error importing products', [
                        'organization_id' => $organizationId,
                        'message' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }

            // Import combo products from comboCategories
            $comboCategories = $nomenclatureData['comboCategories'] ?? [];
            Log::debug('iiko[service]: STEP J — combo categories check', ['count' => count($comboCategories)]);
            if (!empty($comboCategories)) {
                try {
                    $comboStats = $this->importComboProducts($comboCategories, $this->buildCategoryMap());
                    $stats['combo_products_created'] += $comboStats['created'] ?? 0;
                    $stats['products_created'] += $comboStats['created'] ?? 0;
                    Log::debug('iiko[service]: STEP K — combo products imported', $comboStats);
                } catch (\Exception $e) {
                    Log::error('iiko: Error importing combo products', [
                        'organization_id' => $organizationId,
                        'message' => $e->getMessage(),
                    ]);
                    // Non-fatal: continue with the rest of the import
                }
            }

            Log::debug('iiko[service]: STEP L — committing DB transaction');
            DB::commit();

            // Run flat index rebuild AFTER commit (heavy operation, must not hold DB lock)
            Log::debug('iiko[service]: STEP M — rebuilding flat index', ['count' => count($this->productsToRefresh)]);
            foreach ($this->productsToRefresh as $product) {
                try {
                    $this->flatIndexer->refresh($product);
                } catch (\Exception $e) {
                    Log::warning('iiko: flatIndexer refresh failed', ['product_id' => $product->id, 'message' => $e->getMessage()]);
                }
            }

            // Download images AFTER commit (HTTP calls must not hold DB lock)
            Log::debug('iiko[service]: STEP N — downloading images', ['count' => count($this->imagesToDownload)]);
            foreach ($this->imagesToDownload as [$product, $url]) {
                try {
                    $this->downloadAndSaveImage($url, $product);
                } catch (\Exception $e) {
                    Log::warning('iiko: image download failed', ['product_id' => $product->id, 'url' => $url, 'message' => $e->getMessage()]);
                }
            }

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

            // additional must be JSON-encoded: Category model has no JSON cast,
            // so passing a raw PHP array causes Grammar::parameterize() TypeError.
            $categoryDataToSave = [
                'status' => 1,
                'additional' => json_encode(['iiko_id' => $iikoId]),
            ];

            // Set parent_id if parent exists
            if ($parentId) {
                $categoryDataToSave['parent_id'] = $parentId;
            }

            // Set translations for all locales
            $locales = $this->getCachedLocales();
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
        $stats = ['created' => 0, 'updated' => 0, 'grouped_created' => 0, 'constructor_created' => 0, 'configurable_constructor_created' => 0];
        $categoryMap = $this->buildCategoryMap();

        foreach ($items as $itemIndex => $item) {
            // Support both 'id' and 'itemId' from new API format
            $iikoId = $item['id'] ?? $item['itemId'] ?? null;

            if (!$iikoId) {
                Log::debug('iiko[service]: ITEM skip — no iikoId', ['index' => $itemIndex]);
                continue;
            }

            $hasModifiers = $this->hasItemModifierGroups($item);
            $sizePrices = $this->extractSizePrices($item);
            $hasMultipleSizes = count($sizePrices) > 1;

            Log::debug('iiko[service]: ITEM start', [
                'index' => $itemIndex,
                'iikoId' => $iikoId,
                'name' => $item['name'] ?? null,
                'type' => $item['type'] ?? null,
                'hasModifiers' => $hasModifiers,
                'sizePrices_count' => count($sizePrices),
                'hasMultipleSizes' => $hasMultipleSizes,
            ]);

            // Priority 1: Multiple sizes + modifiers → configurable_constructor
            if ($hasModifiers && $hasMultipleSizes) {
                Log::debug('iiko[service]: ITEM → configurable_constructor', ['iikoId' => $iikoId]);
                $result = $this->handleConfigurableConstructorProduct($item, $categoryMap, $sizePrices);
                Log::debug('iiko[service]: ITEM configurable_constructor result', ['iikoId' => $iikoId, 'result' => $result]);
                if ($result) {
                    $stats['configurable_constructor_created']++;
                    if ($result === 'created') {
                        $stats['created']++;
                    } else {
                        $stats['updated']++;
                    }
                }
                continue;
            }

            // Priority 2: Modifiers (single size or no sizes) → constructor
            if ($hasModifiers) {
                Log::debug('iiko[service]: ITEM → constructor', ['iikoId' => $iikoId]);
                $result = $this->handleConstructorProduct($item, $categoryMap);
                Log::debug('iiko[service]: ITEM constructor result', ['iikoId' => $iikoId, 'result' => $result]);
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

            // Use orderItemType as additional signal: Compound suggests modifiers exist
            $orderItemType = strtolower(trim($item['orderItemType'] ?? ''));
            if ($orderItemType === 'compound' && $productType === 'simple') {
                $productType = 'constructor';
            }

            // Priority 3: Multiple prices → grouped
            if ($hasMultipleSizes) {
                Log::debug('iiko[service]: ITEM → grouped (multi-price)', ['iikoId' => $iikoId, 'productType' => $productType]);
                $result = $this->handleMultiPriceProduct($item, $categoryMap);
                Log::debug('iiko[service]: ITEM grouped result', ['iikoId' => $iikoId, 'result' => $result]);
                if ($result) {
                    $stats['grouped_created']++;
                    $stats['created'] += $result['variants_created'] ?? 0;
                }
            } else {
                // Priority 4: Single price product
                $sku = 'iiko_' . $iikoId;
                $existingProduct = $this->findProductByIikoId($iikoId);

                // If not found by iiko_id, check by SKU
                if (!$existingProduct) {
                    $existingProduct = $this->findProductBySku($sku);
                }

                Log::debug('iiko[service]: ITEM → simple/bundle', [
                    'iikoId' => $iikoId,
                    'productType' => $productType,
                    'action' => $existingProduct ? 'update' : 'create',
                ]);

                if ($existingProduct) {
                    $this->updateProduct($existingProduct, $item, $productType, $categoryMap, $sizePrices);
                    $stats['updated']++;
                } else {
                    $this->createProduct($item, $productType, $categoryMap, $sizePrices);
                    $stats['created']++;
                }
                Log::debug('iiko[service]: ITEM simple/bundle done', ['iikoId' => $iikoId]);
            }
        }

        return $stats;
    }

    /**
     * Extract size prices from item (supports both sizePrices and itemSizes formats).
     *
     * @param  array  $item
     * @return array
     */
    protected function extractSizePrices(array $item): array
    {
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

        return $prices;
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
            'combo' => 'bundle',
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

            $channels = $this->getDefaultChannels();

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

                $mainProduct = $this->productRepository->create($mainProductData);
                $mainProduct->refresh();

                // Save attribute values for each locale separately
                $productName = $item['name'] ?? 'Unnamed Product';
                $locales = $this->getCachedLocales();
                $defaultChannelCode = $this->getCachedChannelCode();
                $attributeFamily = $mainProduct->attribute_family;
                $customAttributes = $attributeFamily->custom_attributes;

                foreach ($locales as $locale) {
                    $localeData = [
                        'name' => $productName,
                        'short_description' => $item['description'] ?? null,
                        'description' => $item['description'] ?? null,
                        'status' => 1,
                        'locale' => $locale->code,
                        'channel' => $defaultChannelCode,
                    ];

                    $this->productAttributeValueRepository->saveValues($localeData, $mainProduct, $customAttributes);
                }

                // Ensure inventory exists for main product
                $this->ensureProductInventory($mainProduct);

                // Refresh product_flat index for main product
                $this->queueProductRefresh($mainProduct);
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

                    $variantChannels = $this->getDefaultChannels();

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

                    $variantProduct = $this->productRepository->create($variantData);
                    $variantProduct->refresh();

                    // Save attribute values for each locale separately
                    $defaultChannelCode = $this->getCachedChannelCode();
                    $attributeFamily = $variantProduct->attribute_family;
                    $customAttributes = $attributeFamily->custom_attributes;

                    foreach ($locales as $locale) {
                        $localeData = [
                            'name' => $variantName,
                            'short_description' => $item['description'] ?? null,
                            'description' => $item['description'] ?? null,
                            'price' => $priceValue,
                            'status' => 1,
                            'locale' => $locale->code,
                            'channel' => $defaultChannelCode,
                        ];

                        $this->productAttributeValueRepository->saveValues($localeData, $variantProduct, $customAttributes);
                    }

                    // Ensure inventory exists for variant
                    $this->ensureProductInventory($variantProduct);

                    // Refresh product_flat index for variant
                    $this->queueProductRefresh($variantProduct);

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
            $this->queueProductRefresh($mainProduct);

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
            $channels = $this->getDefaultChannels();

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

                $categoryIikoId = $item['groupId'] ?? null;
                $categories = [];
                if ($categoryIikoId && isset($categoryMap[$categoryIikoId]) && is_numeric($categoryMap[$categoryIikoId]) && $categoryMap[$categoryIikoId] > 0) {
                    $categories[] = (int) $categoryMap[$categoryIikoId];
                }
                $productData['categories'] = $categories;

                // Update basic product data first
                $this->productRepository->update($productData, $existingProduct->id);
                $product = $existingProduct->refresh();

                // Save attribute values for each locale separately
                $locales = $this->getCachedLocales();
                $defaultChannelCode = $this->getCachedChannelCode();
                $attributeFamily = $product->attribute_family;
                $customAttributes = $attributeFamily->custom_attributes;

                foreach ($locales as $locale) {
                    $localeData = [
                        'name' => $productName,
                        'short_description' => $item['description'] ?? null,
                        'description' => $item['description'] ?? null,
                        'price' => $price,
                        'status' => 1,
                        'locale' => $locale->code,
                        'channel' => $defaultChannelCode,
                    ];

                    $this->productAttributeValueRepository->saveValues($localeData, $product, $customAttributes);
                }

                // Ensure inventory exists
                $this->ensureProductInventory($product);

                $result = 'updated';
            } else {

                Log::info('iiko: create product', [
                    '$productName' => $productName,
                    '$item' => $item,
                ]);

                $productData = [
                    'type' => 'constructor',
                    'sku' => $sku,
                    'attribute_family_id' => $attributeFamily->id,
                    'additional' => ['iiko_id' => $iikoId],
                    'channels' => $channels,
                    'status' => 1,
                    'visible_individually' => 1,
                ];

                $product = $this->productRepository->create($productData);
                $product->refresh();

                // Save attribute values for each locale separately
                $locales = $this->getCachedLocales();
                $defaultChannelCode = $this->getCachedChannelCode();
                $attributeFamily = $product->attribute_family;
                $customAttributes = $attributeFamily->custom_attributes;

                foreach ($locales as $locale) {
                    $localeData = [
                        'name' => $productName,
                        'short_description' => $item['description'] ?? null,
                        'description' => $item['description'] ?? null,
                        'price' => $price,
                        'status' => 1,
                        'locale' => $locale->code,
                        'channel' => $defaultChannelCode,
                    ];

                    $this->productAttributeValueRepository->saveValues($localeData, $product, $customAttributes);
                }

                $categoryIikoId = $item['groupId'] ?? null;
                $categories = [];
                if ($categoryIikoId && isset($categoryMap[$categoryIikoId]) && is_numeric($categoryMap[$categoryIikoId]) && $categoryMap[$categoryIikoId] > 0) {
                    $categories[] = (int) $categoryMap[$categoryIikoId];
                }
                $product->categories()->sync($categories);
                $product->refresh();

                // Ensure inventory exists
                $this->ensureProductInventory($product);

                $result = 'created';
            }

            $constructorData = $this->processModifierGroups($modifierGroups, $product->id);
            if (!empty($constructorData)) {
                $this->productConstructorRepository->saveConstructor($constructorData, $product);
            }

            $this->queueProductRefresh($product);
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
     * Handle product with multiple sizes AND modifiers (configurable_constructor).
     *
     * Creates a grouped product (parent) with simple variants per size,
     * each variant gets the constructor (modifier groups) attached.
     *
     * @param  array  $item
     * @param  array  $categoryMap
     * @param  array  $sizePrices
     * @return string|null 'created'|'updated' on success, null on failure
     */
    protected function handleConfigurableConstructorProduct(array $item, array $categoryMap, array $sizePrices): ?string
    {
        try {
            $iikoId = $item['id'] ?? $item['itemId'] ?? null;
            if (!$iikoId) {
                return null;
            }

            $attributeFamily = $this->getDefaultAttributeFamily();
            $channels = $this->getDefaultChannels();
            $productName = $item['name'] ?? 'Unnamed Product';
            $categoryIikoId = $item['groupId'] ?? null;

            $categories = [];
            if ($categoryIikoId && isset($categoryMap[$categoryIikoId]) && is_numeric($categoryMap[$categoryIikoId]) && $categoryMap[$categoryIikoId] > 0) {
                $categories[] = (int) $categoryMap[$categoryIikoId];
            }

            // Check if main grouped product already exists
            $mainSku = 'iiko_' . $iikoId;
            $mainProduct = $this->findProductByIikoId($iikoId);
            if (!$mainProduct) {
                $mainProduct = $this->findProductBySku($mainSku);
            }

            $result = 'updated';

            if (!$mainProduct) {
                $mainProductData = [
                    'type' => 'grouped',
                    'sku' => $mainSku,
                    'attribute_family_id' => $attributeFamily->id,
                    'additional' => ['iiko_id' => $iikoId],
                    'channels' => $channels,
                    'status' => 1,
                    'visible_individually' => 1,
                ];

                $mainProduct = $this->productRepository->create($mainProductData);
                $mainProduct->refresh();
                $result = 'created';
            } else {
                $this->productRepository->update([
                    'additional' => array_merge($mainProduct->additional ?? [], ['iiko_id' => $iikoId]),
                    'channels' => $channels,
                ], $mainProduct->id);
                $mainProduct->refresh();
            }

            // Save attribute values for main product
            $locales = $this->getCachedLocales();
            $defaultChannelCode = $this->getCachedChannelCode();
            $customAttributes = $mainProduct->attribute_family->custom_attributes;

            foreach ($locales as $locale) {
                $this->productAttributeValueRepository->saveValues([
                    'name' => $productName,
                    'short_description' => $item['description'] ?? null,
                    'description' => $item['description'] ?? null,
                    'status' => 1,
                    'locale' => $locale->code,
                    'channel' => $defaultChannelCode,
                ], $mainProduct, $customAttributes);
            }

            $mainProduct->categories()->sync($categories);
            $this->ensureProductInventory($mainProduct);

            // Get price variants category
            $priceVariantsCategory = $this->getOrCreatePriceVariantsCategory();
            $priceVariantCategoryId = $priceVariantsCategory->id ?? null;
            $variantCategories = [];
            if ($priceVariantCategoryId && is_numeric($priceVariantCategoryId) && $priceVariantCategoryId > 0) {
                $variantCategories[] = (int) $priceVariantCategoryId;
            }

            // Extract per-size modifier groups
            $perSizeModifiers = $this->extractPerSizeModifierGroups($item);

            // Create constructor variant for each size
            $variantProducts = [];
            foreach ($sizePrices as $sizeIndex => $sizePrice) {
                $sizeId = $sizePrice['sizeId'] ?? $sizeIndex;
                $sizeName = $sizePrice['sizeName'] ?? '';
                $price = $sizePrice['price'] ?? 0;

                $variantSku = 'iiko_' . $iikoId . '_size_' . $sizeId;
                $variantProduct = $this->findProductBySku($variantSku);

                $variantName = $productName . ($sizeName ? ' - ' . $sizeName : '');

                if (!$variantProduct) {
                    $variantData = [
                        'type' => 'constructor',
                        'sku' => $variantSku,
                        'attribute_family_id' => $attributeFamily->id,
                        'parent_id' => $mainProduct->id,
                        'additional' => [
                            'iiko_id' => $iikoId . '_size_' . $sizeId,
                            'iiko_main_id' => $iikoId,
                            'iiko_size_id' => $sizeId,
                        ],
                        'channels' => $channels,
                        'status' => 1,
                        'visible_individually' => 0,
                    ];

                    $variantProduct = $this->productRepository->create($variantData);
                    $variantProduct->refresh();
                }

                // Save variant attributes
                $variantCustomAttributes = $variantProduct->attribute_family->custom_attributes;
                foreach ($locales as $locale) {
                    $this->productAttributeValueRepository->saveValues([
                        'name' => $variantName,
                        'short_description' => $item['description'] ?? null,
                        'description' => $item['description'] ?? null,
                        'price' => $price,
                        'status' => 1,
                        'locale' => $locale->code,
                        'channel' => $defaultChannelCode,
                    ], $variantProduct, $variantCustomAttributes);
                }

                $variantProduct->categories()->sync($variantCategories);
                $this->ensureProductInventory($variantProduct);

                // Attach modifiers for this size (or fallback to shared modifiers)
                $sizeModifiers = $perSizeModifiers[$sizeId] ?? $perSizeModifiers[array_key_first($perSizeModifiers)] ?? [];
                if (!empty($sizeModifiers)) {
                    $constructorData = $this->processModifierGroups($sizeModifiers, $variantProduct->id);
                    if (!empty($constructorData)) {
                        $this->productConstructorRepository->saveConstructor($constructorData, $variantProduct);
                    }
                }

                $this->queueProductRefresh($variantProduct);
                $variantProducts[] = $variantProduct;
            }

            // Link variants to grouped product
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

            $mainProduct->refresh();
            $this->queueProductRefresh($mainProduct);

            return $result;
        } catch (\Exception $e) {
            Log::error('iiko: Error handling configurable_constructor product', [
                'item_id' => $item['id'] ?? $item['itemId'] ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Extract modifier groups per size from item.
     * Returns array keyed by sizeId with modifier groups for each.
     *
     * @param  array  $item
     * @return array<string, array>
     */
    protected function extractPerSizeModifierGroups(array $item): array
    {
        $result = [];

        // Direct itemModifierGroups apply to all sizes
        if (isset($item['itemModifierGroups']) && is_array($item['itemModifierGroups']) && count($item['itemModifierGroups']) > 0) {
            $result['_shared'] = $item['itemModifierGroups'];
        }

        // Per-size modifiers from itemSizes
        if (isset($item['itemSizes']) && is_array($item['itemSizes'])) {
            foreach ($item['itemSizes'] as $size) {
                $sizeId = $size['sizeId'] ?? null;
                if ($sizeId && isset($size['itemModifierGroups']) && is_array($size['itemModifierGroups']) && count($size['itemModifierGroups']) > 0) {
                    $result[$sizeId] = $size['itemModifierGroups'];
                }
            }
        }

        return $result;
    }

    /**
     * Import combo products from comboCategories section.
     *
     * @param  array  $comboCategories
     * @param  array  $categoryMap
     * @return array
     */
    protected function importComboProducts(array $comboCategories, array $categoryMap): array
    {
        $stats = ['created' => 0, 'updated' => 0];

        foreach ($comboCategories as $comboCategory) {
            $combos = $comboCategory['combos'] ?? [];

            foreach ($combos as $combo) {
                $comboId = $combo['id'] ?? null;
                if (!$comboId) {
                    continue;
                }

                $result = $this->handleComboProduct($combo, $categoryMap);
                if ($result === 'created') {
                    $stats['created']++;
                } elseif ($result === 'updated') {
                    $stats['updated']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Handle a single combo product from comboCategories.
     * Creates a bundle product with groups mapped to bundle options.
     *
     * @param  array  $combo  ComboDto from iiko API
     * @param  array  $categoryMap
     * @return string|null 'created'|'updated' on success, null on failure
     */
    protected function handleComboProduct(array $combo, array $categoryMap): ?string
    {
        try {
            $comboId = $combo['id'];
            $sku = 'iiko_combo_' . $comboId;
            $comboName = $combo['name'] ?? 'Unnamed Combo';
            $price = (float) ($combo['price'] ?? 0);
            $priceStrategy = $combo['priceStrategy'] ?? 'FIXED';

            $existingProduct = $this->findProductByIikoId('combo_' . $comboId);
            if (!$existingProduct) {
                $existingProduct = $this->findProductBySku($sku);
            }

            $attributeFamily = $this->getDefaultAttributeFamily();
            $channels = $this->getDefaultChannels();

            $result = 'updated';

            if (!$existingProduct) {
                $productData = [
                    'type' => 'bundle',
                    'sku' => $sku,
                    'attribute_family_id' => $attributeFamily->id,
                    'additional' => [
                        'iiko_id' => 'combo_' . $comboId,
                        'iiko_combo_id' => $comboId,
                        'iiko_price_strategy' => $priceStrategy,
                    ],
                    'channels' => $channels,
                    'status' => 1,
                    'visible_individually' => 1,
                ];

                $product = $this->productRepository->create($productData);
                $product->refresh();
                $result = 'created';
            } else {
                $this->productRepository->update([
                    'additional' => array_merge($existingProduct->additional ?? [], [
                        'iiko_id' => 'combo_' . $comboId,
                        'iiko_combo_id' => $comboId,
                        'iiko_price_strategy' => $priceStrategy,
                    ]),
                    'channels' => $channels,
                ], $existingProduct->id);
                $product = $existingProduct->refresh();
            }

            // Save attribute values
            $locales = $this->getCachedLocales();
            $defaultChannelCode = $this->getCachedChannelCode();
            $customAttributes = $product->attribute_family->custom_attributes;

            foreach ($locales as $locale) {
                $localeData = [
                    'name' => $comboName,
                    'short_description' => $combo['description'] ?? null,
                    'description' => $combo['description'] ?? null,
                    'status' => 1,
                    'locale' => $locale->code,
                    'channel' => $defaultChannelCode,
                ];

                if ($priceStrategy === 'FIXED' && $price > 0) {
                    $localeData['price'] = $price;
                }

                $this->productAttributeValueRepository->saveValues($localeData, $product, $customAttributes);
            }

            // Build bundle options from combo groups
            $groups = $combo['groups'] ?? [];
            if (!empty($groups)) {
                $bundleOptionsData = $this->buildBundleOptionsFromComboGroups($groups);
                $this->productBundleOptionRepository->saveBundleOptions(
                    ['bundle_options' => $bundleOptionsData],
                    $product
                );
            }

            // Handle combo images
            $images = $combo['image'] ?? [];
            if (!empty($images) && is_array($images)) {
                foreach ($images as $img) {
                    $imgUrl = $img['url'] ?? null;
                    if ($imgUrl) {
                        $this->queueImageDownload($product, $imgUrl);
                        break; // Only download first image
                    }
                }
            }

            $this->ensureProductInventory($product);
            $this->queueProductRefresh($product);

            return $result;
        } catch (\Exception $e) {
            Log::error('iiko: Error handling combo product', [
                'combo_id' => $combo['id'] ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Build bundle options data from iiko combo groups.
     *
     * @param  array  $comboGroups  Array of ComboGroupDto
     * @return array  Bundle options in format expected by saveBundleOptions
     */
    protected function buildBundleOptionsFromComboGroups(array $comboGroups): array
    {
        $bundleOptions = [];
        $optionIndex = 0;

        foreach ($comboGroups as $group) {
            $groupId = $group['id'] ?? null;
            $groupName = $group['name'] ?? 'Unnamed Group';
            $isMainGroup = (bool) ($group['isMainGroup'] ?? false);
            $groupItems = $group['items'] ?? [];

            if (empty($groupItems)) {
                continue;
            }

            $optionProducts = [];
            $productIndex = 0;

            foreach ($groupItems as $groupItem) {
                $itemId = $groupItem['itemId'] ?? null;
                if (!$itemId) {
                    continue;
                }

                // Find the product in our system
                $itemProduct = $this->findProductByIikoId($itemId);
                if (!$itemProduct) {
                    $itemProduct = $this->findProductBySku('iiko_' . $itemId);
                }

                if (!$itemProduct) {
                    continue;
                }

                $optionProducts['product_' . $productIndex] = [
                    'product_id' => $itemProduct->id,
                    'qty' => 1,
                    'is_user_defined' => false,
                    'is_default' => $productIndex === 0,
                    'sort_order' => $productIndex,
                ];
                $productIndex++;
            }

            if (empty($optionProducts)) {
                continue;
            }

            // select = single choice, checkbox = multiple choice
            $optionType = $isMainGroup ? 'select' : 'select';

            $bundleOptions['option_' . $optionIndex] = [
                'type' => $optionType,
                'is_required' => $isMainGroup,
                'sort_order' => $optionIndex,
                'label' => $groupName,
                'products' => $optionProducts,
            ];
            $optionIndex++;
        }

        return $bundleOptions;
    }

    /**
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
        $ingredientData = $this->extractIngredientData($modItem);

        Log::info('iiko: findOrCreateIngredient', [
            '$ingredientData' => $ingredientData,
        ]);

        if (!$ingredientData['sku']) {
            return null;
        }

        // Try to find existing ingredient
        $existing = $this->findIngredientProduct($ingredientData['iikoId'], $ingredientData['ingredientSku']);

        if ($existing) {
            return $this->updateIngredient($existing, $ingredientData);
        }

        // Try to create new ingredient
        try {
            return $this->createIngredient($ingredientData);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle race condition: product was created by another process
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                $existing = $this->findProductBySku($ingredientData['sku']);
                if ($existing) {
                    return $this->updateIngredient($existing, $ingredientData);
                }
            }
            throw $e;
        }
    }

    /**
     * Extract ingredient data from modifier item.
     *
     * @param  array  $modItem
     * @return array
     */
    protected function extractIngredientData(array $modItem): array
    {
        $itemId = $modItem['itemId'] ?? null;
        $sku = $modItem['sku'] ?? null;
        $ingredientSku = $itemId ? 'iiko_' . $itemId : ($sku ? 'iiko_' . $sku : null);

        $price = 0;
        if (isset($modItem['prices']) && is_array($modItem['prices']) && count($modItem['prices']) > 0) {
            $price = (float) ($modItem['prices'][0]['price'] ?? 0);
        }

        return [
            'itemId' => $itemId,
            'sku' => $sku,
            'ingredientSku' => $ingredientSku,
            'iikoId' => $itemId ?? $sku,
            'name' => $modItem['name'] ?? 'Unnamed Ingredient',
            'description' => $modItem['description'] ?? null,
            'price' => $price,
            'buttonImageUrl' => $modItem['buttonImageUrl'] ?? null,
        ];
    }

    /**
     * Find ingredient product by iiko_id or SKU.
     *
     * @param  string|null  $itemId
     * @param  string  $sku
     * @return \Webkul\Product\Contracts\Product|null
     */
    protected function findIngredientProduct(?string $itemId, string $sku)
    {
        if ($itemId) {
            $product = $this->findProductByIikoId($itemId);
            if ($product) {
                return $product;
            }
        }

        return $this->findProductBySku($sku);
    }

    /**
     * Update existing ingredient product.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @param  array  $ingredientData
     * @return \Webkul\Product\Contracts\Product
     */
    protected function updateIngredient($product, array $ingredientData)
    {
        $channels = $this->getDefaultChannels();

        $productData = [
            'additional' => array_merge($product->additional ?? [], ['iiko_id' => $ingredientData['iikoId']]),
            'channels' => $channels,
        ];

        $this->productRepository->update($productData, $product->id);
        $product->refresh();

        $this->saveIngredientAttributes($product, $ingredientData);
        $this->handleIngredientImage($product, $ingredientData['buttonImageUrl']);

        // Ensure inventory exists
        $this->ensureProductInventory($product);

        $this->queueProductRefresh($product);

        return $product;
    }

    /**
     * Create new ingredient product.
     *
     * @param  array  $ingredientData
     * @return \Webkul\Product\Contracts\Product
     */
    protected function createIngredient(array $ingredientData)
    {
        $attributeFamily = $this->getDefaultAttributeFamily();
        $channels = $this->getDefaultChannels();

        $productData = [
            'type' => 'ingredient',
            'sku' => $ingredientData['ingredientSku'],
            'attribute_family_id' => $attributeFamily->id,
            'additional' => ['iiko_id' => $ingredientData['iikoId']],
            'channels' => $channels,
            'status' => 1,
            'visible_individually' => 0,
        ];

        $product = $this->productRepository->create($productData);
        $product->refresh();

        $this->saveIngredientAttributes($product, $ingredientData);
        $this->handleIngredientImage($product, $ingredientData['buttonImageUrl']);

        // Ensure inventory exists
        $this->ensureProductInventory($product);

        $this->queueProductRefresh($product);

        return $product;
    }

    /**
     * Save ingredient product attributes for all locales.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @param  array  $ingredientData
     * @return void
     */
    protected function saveIngredientAttributes($product, array $ingredientData): void
    {
        $locales = $this->getCachedLocales();
        $defaultChannelCode = $this->getCachedChannelCode();
        $attributeFamily = $product->attribute_family;
        $customAttributes = $attributeFamily->custom_attributes;

        foreach ($locales as $locale) {
            $localeData = [
                'name' => $ingredientData['name'],
                'short_description' => $ingredientData['description'],
                'description' => $ingredientData['description'],
                'price' => $ingredientData['price'],
                'status' => 1,
                'locale' => $locale->code,
                'channel' => $defaultChannelCode,
            ];

            $this->productAttributeValueRepository->saveValues($localeData, $product, $customAttributes);
        }
    }

    /**
     * Handle ingredient image download and save.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @param  string|null  $imageUrl
     * @return void
     */
    protected function handleIngredientImage($product, ?string $imageUrl): void
    {
        $this->queueImageDownload($product, $imageUrl);
    }

    /**
     * Get default channels array.
     *
     * @return array
     */
    protected function getDefaultChannels(): array
    {
        $channelId = $this->getCachedChannelId();
        return $channelId ? [$channelId] : [];
    }

    protected function getCachedLocales(): array
    {
        if ($this->cachedLocales === null) {
            $this->cachedLocales = $this->getCachedLocales()->all();
        }

        return $this->cachedLocales;
    }

    protected function getCachedChannelCode(): string
    {
        if ($this->cachedChannelCode === null) {
            $this->cachedChannelCode = $this->getCachedChannelCode();
        }

        return $this->cachedChannelCode;
    }

    protected function getCachedChannelId(): ?int
    {
        if ($this->cachedChannelId === null) {
            $id = core()->getDefaultChannel()->id ?? null;
            $this->cachedChannelId = ($id && is_numeric($id) && $id > 0) ? (int) $id : 0;
        }

        return $this->cachedChannelId > 0 ? $this->cachedChannelId : null;
    }

    protected function queueProductRefresh($product): void
    {
        $this->productsToRefresh[$product->id] = $product;
    }

    protected function queueImageDownload($product, ?string $url): void
    {
        if (!empty($url)) {
            $this->imagesToDownload[] = [$product, $url];
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
        $channels = $this->getDefaultChannels();

        $productData = [
            'type' => $productType,
            'sku' => $sku,
            'attribute_family_id' => $attributeFamily->id,
            'additional' => ['iiko_id' => $iikoId],
            'channels' => $channels,
            'status' => 1,
            'visible_individually' => 1,
        ];

        $product = $this->productRepository->create($productData);
        $product->refresh();

        // Save attribute values for each locale separately
        $productName = $item['name'] ?? 'Unnamed Product';
        $locales = $this->getCachedLocales();
        $defaultChannelCode = $this->getCachedChannelCode();
        $attributeFamily = $product->attribute_family;
        $customAttributes = $attributeFamily->custom_attributes;

        foreach ($locales as $locale) {
            $localeData = [
                'name' => $productName,
                'short_description' => $item['description'] ?? null,
                'description' => $item['description'] ?? null,
                'price' => $price,
                'status' => 1,
                'locale' => $locale->code,
                'channel' => $defaultChannelCode,
            ];

            $this->productAttributeValueRepository->saveValues($localeData, $product, $customAttributes);
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
        $product->categories()->sync($categories);

        // Ensure inventory exists
        $this->ensureProductInventory($product);

        // Refresh product_flat index after creating product and setting categories
        $product->refresh();
        $this->queueProductRefresh($product);
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
        // Get import settings
        $channelCode = $this->getCachedChannelCode();
        $settings = $this->settingRepository->getAllSettings(IikoSetting::CHANNEL, $channelCode);

        $price = !empty($prices) ? ($prices[0]['price'] ?? 0) : 0;
        $productName = $item['name'] ?? 'Unnamed Product';

        // Support both 'id' and 'itemId' from new API format
        $itemId = $item['id'] ?? $item['itemId'] ?? null;
        $channels = $this->getDefaultChannels();

        $productData = [
            'type' => $productType,
            'additional' => array_merge($product->additional ?? [], ['iiko_id' => $itemId]),
            'channels' => $channels,
        ];

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

        // Update basic product data first
        $this->productRepository->update($productData, $product->id);
        $product->refresh();

        // Save attribute values for each locale separately
        $locales = $this->getCachedLocales();
        $defaultChannelCode = $this->getCachedChannelCode();
        $attributeFamily = $product->attribute_family;
        $customAttributes = $attributeFamily->custom_attributes;

        foreach ($locales as $locale) {
            $localeData = [
                'status' => 1,
                'locale' => $locale->code,
                'channel' => $defaultChannelCode,
            ];

            // Update product name if setting allows
            if ($settings['update_product_name'] ?? true) {
                $localeData['name'] = $productName;
            }

            // Update product description if setting allows
            if ($settings['update_product_description'] ?? true) {
                $localeData['short_description'] = $item['description'] ?? null;
                $localeData['description'] = $item['description'] ?? null;
            }

            // Update product price if setting allows
            if ($settings['update_product_price'] ?? true) {
                $localeData['price'] = $price;
            }

            $this->productAttributeValueRepository->saveValues($localeData, $product, $customAttributes);
        }

        // Update product image if setting allows (queued — runs after DB commit)
        if (($settings['update_product_image'] ?? true) && !empty($item['imageUrl'])) {
            $this->queueImageDownload($product, $item['imageUrl']);
        }

        // Update nutritional values (КЖБУ) if setting allows
        if ($settings['update_product_nutritional'] ?? true) {
            $this->updateProductNutritional($product, $item);
        }

        // Ensure inventory exists
        $this->ensureProductInventory($product);

        // Refresh product_flat index after updating product
        $product->refresh();
        $this->queueProductRefresh($product);
    }

    /**
     * Update product nutritional values (КЖБУ).
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @param  array  $item
     * @return void
     */
    protected function updateProductNutritional($product, array $item): void
    {
        $locales = $this->getCachedLocales();
        $defaultChannelCode = $this->getCachedChannelCode();
        $attributeFamily = $product->attribute_family;
        $customAttributes = $attributeFamily->custom_attributes;

        // Extract nutritional values from item data
        // Try different possible field names from iiko API
        $nutritionalData = [];

        // Calories (калории)
        $calories = $item['calories']
            ?? $item['energy']
            ?? $item['energyValue']
            ?? $item['nutritionalInfo']['calories']
            ?? $item['nutritionalInfo']['energy']
            ?? null;

        if ($calories !== null && is_numeric($calories)) {
            $nutritionalData['calories'] = (float) $calories;
        }

        // Proteins (белки)
        $proteins = $item['proteins']
            ?? $item['protein']
            ?? $item['nutritionalInfo']['proteins']
            ?? $item['nutritionalInfo']['protein']
            ?? null;

        if ($proteins !== null && is_numeric($proteins)) {
            $nutritionalData['proteins'] = (float) $proteins;
        }

        // Fats (жиры)
        $fats = $item['fats']
            ?? $item['fat']
            ?? $item['nutritionalInfo']['fats']
            ?? $item['nutritionalInfo']['fat']
            ?? null;

        if ($fats !== null && is_numeric($fats)) {
            $nutritionalData['fats'] = (float) $fats;
        }

        // Carbohydrates (углеводы)
        $carbs = $item['carbs']
            ?? $item['carbohydrates']
            ?? $item['carbohydrate']
            ?? $item['nutritionalInfo']['carbs']
            ?? $item['nutritionalInfo']['carbohydrates']
            ?? $item['nutritionalInfo']['carbohydrate']
            ?? null;

        if ($carbs !== null && is_numeric($carbs)) {
            $nutritionalData['carbs'] = (float) $carbs;
        }

        // Update nutritional attributes if we have any data
        if (!empty($nutritionalData)) {
            foreach ($locales as $locale) {
                $localeData = array_merge($nutritionalData, [
                    'locale' => $locale->code,
                    'channel' => $defaultChannelCode,
                ]);

                $this->productAttributeValueRepository->saveValues($localeData, $product, $customAttributes);
            }
        }
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
        $locales = $this->getCachedLocales();
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
        if ($this->cachedAttributeFamily !== null) {
            return $this->cachedAttributeFamily;
        }

        $family = $this->attributeFamilyRepository->findWhere(['code' => 'default'])->first();

        if (!$family) {
            $family = $this->attributeFamilyRepository->first();
        }

        if (!$family) {
            throw new \Exception('No attribute family found');
        }

        return $this->cachedAttributeFamily = $family;
    }

    /**
     * Ensure product has inventory record.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    protected function ensureProductInventory($product): void
    {
        // Load inventories relationship if not already loaded
        if (!$product->relationLoaded('inventories')) {
            $product->load('inventories');
        }

        // Check if product has any inventory records
        if ($product->inventories->count() == 0) {
            // Create a default inventory record
            $this->productInventoryRepository->create([
                'product_id' => $product->id,
                'vendor_id' => 0,
                'inventory_source_id' => 1, // Default inventory source
                'qty' => 999, // High quantity to show as available
            ]);
        } else {
            // Update existing inventory record to ensure it shows as available
            foreach ($product->inventories as $inventory) {
                if ($inventory->qty <= 0) {
                    $inventory->update(['qty' => 999]);
                }
            }
        }
    }
}
