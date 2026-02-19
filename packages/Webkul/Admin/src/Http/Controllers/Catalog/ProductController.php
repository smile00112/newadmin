<?php

namespace Webkul\Admin\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\DataGrids\Catalog\ProductDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\InventoryRequest;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Admin\Http\Requests\ProductForm;
use Webkul\Admin\Http\Resources\AttributeResource;
use Webkul\Admin\Http\Resources\ProductResource;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Core\Rules\Slug;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Helpers\Product;
use Webkul\Product\Helpers\ProductType;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Webkul\Product\Repositories\ProductDownloadableLinkRepository;
use Webkul\Product\Repositories\ProductDownloadableSampleRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryController;

class ProductController extends Controller
{
    /**
     * Using const variable for status.
     */
    const ACTIVE_STATUS = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeFamilyRepository $attributeFamilyRepository,
        protected ProductAttributeValueRepository $productAttributeValueRepository,
        protected ProductDownloadableLinkRepository $productDownloadableLinkRepository,
        protected ProductDownloadableSampleRepository $productDownloadableSampleRepository,
        protected ProductInventoryRepository $productInventoryRepository,
        protected ProductRepository $productRepository,
        protected CustomerRepository $customerRepository,
        protected \Webkul\Product\Repositories\ProductIngredientsIncompatibilityTemplateRepository $incompatibilityTemplateRepository,
        protected \Webkul\Product\Repositories\ProductConstructorGroupTemplateRepository $constructorGroupTemplateRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ProductDataGrid::class)->process();
        }

        $families = $this->attributeFamilyRepository->all();

        return view('admin::catalog.products.index', compact('families'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $families = $this->attributeFamilyRepository->all();

        $configurableFamily = null;

        if ($familyId = request()->get('family')) {
            $configurableFamily = $this->attributeFamilyRepository->find($familyId);
        }

        return view('admin::catalog.products.create', compact('families', 'configurableFamily'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $this->validate(request(), [
            'type'                => 'required',
            'attribute_family_id' => 'required',
            'sku'                 => ['required', 'unique:products,sku', new Slug],
            'super_attributes'    => 'array|min:1',
            'super_attributes.*'  => 'array|min:1',
        ]);

        if (
            ProductType::hasVariants(request()->input('type'))
            && ! request()->has('super_attributes')
        ) {
            $configurableFamily = $this->attributeFamilyRepository
                ->find(request()->input('attribute_family_id'));

            return new JsonResponse([
                'data' => [
                    'attributes' => AttributeResource::collection($configurableFamily->configurable_attributes),
                ],
            ]);
        }

        Event::dispatch('catalog.product.create.before');

        $product = $this->productRepository->create(request()->only([
            'type',
            'attribute_family_id',
            'sku',
            'super_attributes',
            'family',
        ]));

        Event::dispatch('catalog.product.create.after', $product);

        session()->flash('success', trans('admin::app.catalog.products.create-success'));

        return new JsonResponse([
            'data' => [
                'redirect_url' => route('admin.catalog.products.edit', $product->id),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $product = $this->productRepository->findOrFail($id);
        
        // Load incompatibility templates for constructor type
        $incompatibilityTemplates = $this->incompatibilityTemplateRepository
            ->where('active', 1)
            ->orderBy('name')
            ->get();
        
        // Load constructor group templates
        $constructorGroupTemplates = $this->constructorGroupTemplateRepository
            ->with('products')
            ->orderBy('template_name')
            ->get();

        return view('admin::catalog.products.edit', compact('product', 'incompatibilityTemplates', 'constructorGroupTemplates'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProductForm $request, int $id)
    {
        Event::dispatch('catalog.product.update.before', $id);

        $product = $this->productRepository->update(request()->all(), $id);

        Event::dispatch('catalog.product.update.after', $product);

        // Clear catalog API cache after product update
        try {
            Log::info('Clearing catalog cache after product update', ['product_id' => $product->id]);
            CatalogCategoryController::clearCatalogCache();
            Log::info('Catalog cache cleared successfully', ['product_id' => $product->id]);
        } catch (\Exception $e) {
            Log::error('Failed to clear catalog cache after product update', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }

        session()->flash('success', trans('admin::app.catalog.products.update-success'));

        //dd('save');
        /*TODO refactor*/
        if($product->type !=='ingredient'){
            return redirect()->route('admin.catalog.products.index');
        }else{
            return redirect('/admin/catalog/products?ingredient=1');
        }
    }

    /**
     * Update inventories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInventories(InventoryRequest $inventoryRequest, int $id)
    {
        $product = $this->productRepository->findOrFail($id);

        Event::dispatch('catalog.product.update.before', $id);

        $this->productInventoryRepository->saveInventories(request()->all(), $product);

        Event::dispatch('catalog.product.update.after', $product);

        // Clear catalog cache after inventory update
        CatalogCategoryController::clearCatalogCache();

        return response()->json([
            'message'      => __('admin::app.catalog.products.saved-inventory-message'),
            'updatedTotal' => $this->productInventoryRepository->where('product_id', $product->id)->sum('qty'),
        ]);
    }

    /**
     * Uploads downloadable file.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadLink(int $id)
    {
        return response()->json(
            $this->productDownloadableLinkRepository->upload(request()->all(), $id)
        );
    }

    /**
     * Copy a given Product.
     *
     * @return \Illuminate\Http\jsonResponse
     */
    public function copy(int $id)
    {
        try {
            Event::dispatch('catalog.product.create.before');

            $product = $this->productRepository->copy($id);

            Event::dispatch('catalog.product.create.after', $product);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->to(route('admin.catalog.products.index'));
        }

        return response()->json([
            'message' => trans('admin::app.catalog.products.product-copied'),
        ]);
    }

    /**
     * Uploads downloadable sample file.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadSample(int $id)
    {
        return response()->json(
            $this->productDownloadableSampleRepository->upload(request()->all(), $id)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            Event::dispatch('catalog.product.delete.before', $id);

            $this->productRepository->delete($id);

            Event::dispatch('catalog.product.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.catalog.products.delete-success'),
            ]);
        } catch (\Exception $e) {
            report($e);
        }

        return new JsonResponse([
            'message' => trans('admin::app.catalog.products.delete-failed'),
        ], 500);
    }

    /**
     * Mass delete the products.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $productIds = $massDestroyRequest->input('indices');

        try {
            foreach ($productIds as $productId) {
                $product = $this->productRepository->find($productId);

                if (isset($product)) {
                    Event::dispatch('catalog.product.delete.before', $productId);

                    $this->productRepository->delete($productId);

                    Event::dispatch('catalog.product.delete.after', $productId);
                }
            }

            return new JsonResponse([
                'message' => trans('admin::app.catalog.products.index.datagrid.mass-delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mass update the products.
     */
    public function massUpdate(MassUpdateRequest $massUpdateRequest): JsonResponse
    {
        $productIds = $massUpdateRequest->input('indices');

        foreach ($productIds as $productId) {
            Event::dispatch('catalog.product.update.before', $productId);

            $product = $this->productRepository->update([
                'status'  => $massUpdateRequest->input('value'),
            ], $productId, ['status']);

            Event::dispatch('catalog.product.update.after', $product);
        }

        return new JsonResponse([
            'message' => trans('admin::app.catalog.products.index.datagrid.mass-update-success'),
        ], 200);
    }

    /**
     * To be manually invoked when data is seeded into products.
     *
     * @return \Illuminate\Http\Response
     */
    public function sync()
    {
        Event::dispatch('products.datagrid.sync', true);

        return redirect()->route('admin.catalog.products.index');
    }

    /**
     * Get constructor group template data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConstructorGroupTemplate(int $id): JsonResponse
    {
        $locale = app()->getLocale();
        $channel = core()->getCurrentChannelCode();
        
        $template = $this->constructorGroupTemplateRepository->findOrFail($id);
        
        // Load products with images
        $template->load(['products.images']);
        
        // Transform products to include pivot data and names from product_flat
        $products = $template->products->map(function ($product) use ($locale, $channel) {
            // Get name from product_flat
            $productFlat = \DB::table('product_flat')
                ->where('product_id', $product->id)
                ->where('locale', $locale)
                ->where('channel', $channel)
                ->first();
            
            return [
                'id'      => $product->id,
                'name'    => $productFlat->name ?? $product->sku,
                'sku'     => $product->sku,
                'sort'    => $product->pivot->sort ?? 0,
                'default' => (bool) ($product->pivot->default ?? false),
                'images'  => $product->images ?? [],
            ];
        });
        
        return new JsonResponse([
            'data' => [
                'name'                            => $template->name,
                'field_type'                      => $template->field_type,
                'checked_type'                    => $template->checked_type,
                'quantity_min'                    => $template->quantity_min,
                'quantity_max'                    => $template->quantity_max,
                'show_title'                      => $template->show_title,
                'opened_by_default'               => $template->opened_by_default,
                'zero_price'                      => $template->zero_price,
                'required'                        => $template->required,
                'hidden'                          => $template->hidden,
                'double_portions'                 => $template->double_portions,
                'half_portions'                   => $template->half_portions,
                'ingredients_incompatibilities_id' => $template->ingredients_incompatibilities_id,
                'sort'                            => $template->sort,
                'products'                        => $products,
            ],
        ]);
    }

    /**
     * Save constructor group as template.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveGroupAsTemplate(): JsonResponse
    {
        $data = request()->all();
        
        // Validate required fields
        if (empty($data['name'])) {
            return new JsonResponse([
                'success' => false,
                'message' => trans('admin::app.catalog.products.edit.types.constructor.template-name-required'),
            ], 400);
        }
        
        try {
            // Prepare template data
            $templateData = [
                'template_name'                    => $data['name'], // Use group name as template name
                'name'                             => $data['name'],
                'field_type'                       => $data['field_type'] ?? 'checkbox',
                'checked_type'                     => $data['checked_type'] ?? 'once',
                'quantity_min'                     => $data['quantity_min'] ?? 0,
                'quantity_max'                     => $data['quantity_max'] ?? 0,
                'show_title'                       => $data['show_title'] ?? false,
                'opened_by_default'                => $data['opened_by_default'] ?? false,
                'zero_price'                       => $data['zero_price'] ?? false,
                'required'                         => $data['required'] ?? false,
                'hidden'                           => $data['hidden'] ?? false,
                'double_portions'                  => $data['double_portions'] ?? false,
                'half_portions'                    => $data['half_portions'] ?? false,
                'ingredients_incompatibilities_id' => !empty($data['ingredients_incompatibilities_id']) 
                    ? $data['ingredients_incompatibilities_id'] 
                    : null,
                'sort'                             => $data['sort'] ?? 0,
            ];
            
            // Create template
            $template = $this->constructorGroupTemplateRepository->create($templateData);
            
            // Sync products with pivot data
            if (!empty($data['products']) && is_array($data['products'])) {
                $productsData = [];
                foreach ($data['products'] as $product) {
                    if (!empty($product['id'])) {
                        $productsData[$product['id']] = [
                            'sort'    => $product['sort'] ?? 0,
                            'default' => !empty($product['default']) ? 1 : 0,
                        ];
                    }
                }
                $template->products()->sync($productsData);
            }
            
            return new JsonResponse([
                'success' => true,
                'message' => trans('admin::app.catalog.products.edit.types.constructor.template-saved-successfully'),
                'template' => [
                    'id'   => $template->id,
                    'name' => $template->template_name,
                ],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Result of search product.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        $query = trim(request('query'));
        $limit = request('limit', 30);

        $searchEngine = 'database';

        if (
            core()->getConfigData('catalog.products.search.engine') == 'elastic'
            && core()->getConfigData('catalog.products.search.admin_mode') == 'elastic'
        ) {
            $searchEngine = 'elastic';

            $indexNames = core()->getAllChannels()->map(function ($channel) {
                return Product::formatElasticSearchIndexName($channel->code, app()->getLocale());
            })->toArray();
        }

        $channelId = $this->customerRepository->find(request('customer_id'))->channel_id ?? null;

        $params = [
            'index'      => $indexNames ?? null,
            'name'       => $query ?: null,
            'sort'       => 'created_at',
            'order'      => 'desc',
            'channel_id' => $channelId,
            'limit'      => $limit,
        ];

        if (request()->has('type')) {
            $params['type'] = request('type');
        }

        if (request()->has('exclude_customizable_products')) {
            $params['exclude_customizable_products'] = request('exclude_customizable_products');
        }

        if (request()->has('exclude_type')) {
            $params['exclude_type'] = request('exclude_type');
        }

        $products = $this->productRepository
            ->setSearchEngine($searchEngine)
            ->getAll($params);

        return ProductResource::collection($products);
    }

    /*TODO - refactor this*/
    public function search_ingredients()
    {
        $query = trim(request('query'));

        if (empty($query)) {
            return response()->json([
                'data' => [],
            ]);
        }

        $searchEngine = 'database';

        if (
            core()->getConfigData('catalog.products.search.engine') == 'elastic'
            && core()->getConfigData('catalog.products.search.admin_mode') == 'elastic'
        ) {
            $searchEngine = 'elastic';

            $indexNames = core()->getAllChannels()->map(function ($channel) {
                return Product::formatElasticSearchIndexName($channel->code, app()->getLocale());
            })->toArray();
        }

        $channelId = $this->customerRepository->find(request('customer_id'))->channel_id ?? null;

        $params = [
            'index'      => $indexNames ?? null,
            'name'       => request('query'),
            'sort'       => 'created_at',
            'order'      => 'desc',
            'channel_id' => $channelId,
        ];

        if (request()->has('type')) {
            $params['type'] = request('type');
        }

        if (request()->has('exclude_customizable_products')) {
            $params['exclude_customizable_products'] = request('exclude_customizable_products');
        }

        $products = $this->productRepository
            ->setSearchEngine($searchEngine)
            ->getAll($params);

        return ProductResource::collection($products);
    }


    /**
     * Download image or file.
     *
     * @param  int  $productId
     * @param  int  $attributeId
     * @return \Illuminate\Http\Response
     */
    public function download($productId, $attributeId)
    {
        $productAttribute = $this->productAttributeValueRepository->findOneWhere([
            'product_id'   => $productId,
            'attribute_id' => $attributeId,
        ]);

        return Storage::download($productAttribute['text_value']);
    }
}
