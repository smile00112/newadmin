<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Models\CoreConfig;
use Webkul\MobileApp\Http\Controllers\Api\MobileSettingsController as MobileSettingsApiController;
use Webkul\Product\Repositories\ProductRepository;

class ProductCategoryPositionsController extends Controller
{
    private const CONFIG_CODE = 'catalog.product_category_positions';

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Display the product category positions settings page.
     */
    public function index(): View
    {
        $mappings = $this->getMappings();
        $categories = $this->getCategoriesForSelect();
        $productsData = $this->getProductsDataForMappings($mappings);

        return view('admin::settings.product_category_positions', compact(
            'mappings',
            'categories',
            'productsData'
        ));
    }

    /**
     * Store product category positions.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mappings' => 'nullable|array',
            'mappings.*.product_id' => 'nullable|integer|exists:products,id',
            'mappings.*.category_id' => 'nullable|integer|exists:categories,id',
            'mappings.*.position_type' => 'nullable|in:top,middle,bottom,numeric',
            'mappings.*.position_value' => 'nullable|integer|min:0',
        ]);

        $mappings = $validated['mappings'] ?? [];
        $result = [];

        foreach ($mappings as $row) {
            if (empty($row['product_id']) || empty($row['category_id'])) {
                continue;
            }

            $result[] = [
                'product_id' => (int) $row['product_id'],
                'category_id' => (int) $row['category_id'],
                'position_type' => $row['position_type'],
                'position_value' => $row['position_type'] === 'numeric'
                    ? (int) ($row['position_value'] ?? 0)
                    : null,
            ];
        }

        $config = CoreConfig::firstOrNew(
            [
                'code' => self::CONFIG_CODE,
                'channel_code' => null,
                'locale_code' => null,
            ],
            ['value' => '[]']
        );

        $config->value = json_encode($result);
        $config->save();

        MobileSettingsApiController::clearCache();

        session()->flash('success', trans('admin::app.configuration.index.save-message'));

        return redirect()->route('admin.settings.product_category_positions.index');
    }

    /**
     * Get mappings from core_config.
     *
     * @return array<int, array{product_id: int, category_id: int, position_type: string, position_value: int|null}>
     */
    private function getMappings(): array
    {
        $config = CoreConfig::where('code', self::CONFIG_CODE)
            ->whereNull('channel_code')
            ->whereNull('locale_code')
            ->first();

        if (! $config || empty($config->value)) {
            return [];
        }

        $decoded = json_decode($config->value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get categories as flat array for select.
     *
     * @return array<int, array{id: int, name: string, depth: int}>
     */
    private function getCategoriesForSelect(): array
    {
        $tree = $this->categoryRepository->getVisibleCategoryTree();
        $flat = [];

        $flatten = function ($items, $depth = 0) use (&$flatten, &$flat) {
            foreach ($items as $item) {
                $flat[] = [
                    'id' => $item->id,
                    'name' => $item->name ?: $item->id,
                    'depth' => $depth,
                ];
                if ($item->children && $item->children->isNotEmpty()) {
                    $flatten($item->children->all(), $depth + 1);
                }
            }
        };

        $flatten($tree->all());

        return $flat;
    }

    /**
     * Get product data for existing mappings.
     *
     * @param  array<int, array{product_id: int}>  $mappings
     * @return array<int, array{id: int, name: string, sku: string, images: array}>
     */
    private function getProductsDataForMappings(array $mappings): array
    {
        $productIds = array_unique(array_column($mappings, 'product_id'));

        if (empty($productIds)) {
            return [];
        }

        $products = $this->productRepository
            ->whereIn('id', $productIds)
            ->with('images')
            ->get();

        $result = [];
        foreach ($products as $product) {
            $result[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'images' => $product->images->map(fn ($img) => ['url' => $img->url])->toArray(),
            ];
        }

        return $result;
    }
}
