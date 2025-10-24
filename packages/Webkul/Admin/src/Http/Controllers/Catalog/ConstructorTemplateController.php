<?php

namespace Webkul\Admin\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\DataGrids\Catalog\ConstructorGroupTemplateDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Product\Repositories\ProductConstructorGroupTemplateRepository;
use Webkul\Product\Repositories\ProductIngredientsIncompatibilityTemplateRepository;
use Webkul\Product\Repositories\ProductRepository;

class ConstructorTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ProductConstructorGroupTemplateRepository $templateRepository,
        protected ProductIngredientsIncompatibilityTemplateRepository $incompatibilityTemplateRepository,
        protected ProductRepository $productRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ConstructorGroupTemplateDataGrid::class)->process();
        }

        return view('admin::catalog.constructor-templates.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $incompatibilityTemplates = $this->incompatibilityTemplateRepository
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        return view('admin::catalog.constructor-templates.create', compact('incompatibilityTemplates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $this->validate(request(), [
            'template_name' => 'required|max:150',
            'name'          => 'nullable|max:150',
            'field_type'    => 'required|in:checkbox,radio,list',
            'checked_type'  => 'required|in:once,multiple',
        ]);

        Event::dispatch('catalog.constructor_template.create.before');

        $data = request()->only([
            'template_name',
            'name',
            'field_type',
            'checked_type',
            'quantity_min',
            'quantity_max',
            'show_title',
            'opened_by_default',
            'zero_price',
            'required',
            'hidden',
            'double_portions',
            'half_portions',
            'sort',
            'ingredients_incompatibilities_id',
        ]);

        // Convert checkboxes to boolean
        foreach (['show_title', 'opened_by_default', 'zero_price', 'required', 'hidden', 'double_portions', 'half_portions'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        // Convert empty string to null for foreign key
        if (empty($data['ingredients_incompatibilities_id'])) {
            $data['ingredients_incompatibilities_id'] = null;
        }

        $template = $this->templateRepository->create($data);

        // Sync products
        $this->syncProducts($template->id);

        Event::dispatch('catalog.constructor_template.create.after', $template);

        session()->flash('success', trans('admin::app.catalog.constructor-templates.create.success'));

        return redirect()->route('admin.catalog.constructor_templates.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $locale = app()->getLocale();
        $channel = core()->getCurrentChannelCode();
        
        $template = $this->templateRepository->findOrFail($id);
        
        // Загружаем продукты с названиями из product_flat и изображениями
        $template->load(['products' => function ($query) use ($locale, $channel) {
            $query->select('products.id', 'products.sku')
                  ->with('images')
                  ->join('product_flat', 'products.id', '=', 'product_flat.product_id')
                  ->where('product_flat.locale', $locale)
                  ->where('product_flat.channel', $channel)
                  ->addSelect('product_flat.name');
        }]);
        
        $incompatibilityTemplates = $this->incompatibilityTemplateRepository
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        return view('admin::catalog.constructor-templates.edit', compact('template', 'incompatibilityTemplates'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id)
    {
        $this->validate(request(), [
            'template_name' => 'required|max:150',
            'name'          => 'nullable|max:150',
            'field_type'    => 'required|in:checkbox,radio,list',
            'checked_type'  => 'required|in:once,multiple',
        ]);

        Event::dispatch('catalog.constructor_template.update.before', $id);

        $data = request()->only([
            'template_name',
            'name',
            'field_type',
            'checked_type',
            'quantity_min',
            'quantity_max',
            'show_title',
            'opened_by_default',
            'zero_price',
            'required',
            'hidden',
            'double_portions',
            'half_portions',
            'sort',
            'ingredients_incompatibilities_id',
        ]);

        // Convert checkboxes to boolean
        foreach (['show_title', 'opened_by_default', 'zero_price', 'required', 'hidden', 'double_portions', 'half_portions'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        // Convert empty string to null for foreign key
        if (empty($data['ingredients_incompatibilities_id'])) {
            $data['ingredients_incompatibilities_id'] = null;
        }

        $template = $this->templateRepository->update($data, $id);

        // Sync products
        $this->syncProducts($id);

        Event::dispatch('catalog.constructor_template.update.after', $template);

        session()->flash('success', trans('admin::app.catalog.constructor-templates.edit.success'));

        return redirect()->route('admin.catalog.constructor_templates.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $template = $this->templateRepository->findOrFail($id);

        Event::dispatch('catalog.constructor_template.delete.before', $id);

        $template->delete();

        Event::dispatch('catalog.constructor_template.delete.after', $id);

        return new JsonResponse([
            'message' => trans('admin::app.catalog.constructor-templates.delete.success'),
        ]);
    }

    /**
     * Remove the specified resources from database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest)
    {
        $indices = $massDestroyRequest->input('indices', []);

        foreach ($indices as $index) {
            Event::dispatch('catalog.constructor_template.delete.before', $index);

            $this->templateRepository->delete($index);

            Event::dispatch('catalog.constructor_template.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('admin::app.catalog.constructor-templates.delete.success-mass'),
        ]);
    }

    /**
     * Search products for template
     */
    public function searchProducts(): JsonResponse
    {
        $query = request()->input('query', '');
        $limit = request()->input('limit', 30);
        $locale = app()->getLocale();
        $channel = core()->getCurrentChannelCode();

        $queryBuilder = DB::table('product_flat')
            ->join('products', 'product_flat.product_id', '=', 'products.id')
            ->where('products.type', 'ingredient')
            ->where('product_flat.locale', $locale)
            ->where('product_flat.channel', $channel);

        if (!empty($query)) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('product_flat.name', 'like', '%'.$query.'%')
                  ->orWhere('products.sku', 'like', '%'.$query.'%');
            });
        }

        $products = $queryBuilder
            ->select('products.id', 'products.sku', 'product_flat.name')
            ->orderBy('product_flat.name')
            ->limit($limit)
            ->get();

        return new JsonResponse([
            'data' => $products,
        ]);
    }

    /**
     * Sync products for a template
     *
     * @param  int  $templateId
     * @return void
     */
    protected function syncProducts(int $templateId): void
    {
        $products = request()->input('products', []);
        
        // Prepare sync data with pivot values
        $syncData = [];
        
        foreach ($products as $productData) {
            if (!empty($productData['id'])) {
                $syncData[$productData['id']] = [
                    'sort'    => $productData['sort'] ?? 0,
                    'default' => (bool) ($productData['default'] ?? false),
                ];
            }
        }
        
        $template = $this->templateRepository->findOrFail($templateId);
        $template->products()->sync($syncData);
    }
}

