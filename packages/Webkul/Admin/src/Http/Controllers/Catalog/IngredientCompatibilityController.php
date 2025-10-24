<?php

namespace Webkul\Admin\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\DataGrids\Catalog\IngredientIncompatibilityTemplateDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Product\Repositories\ProductIngredientsIncompatibilityTemplateRepository;
use Webkul\Product\Repositories\ProductIngredientsIncompatibilityRepository;
use Webkul\Product\Repositories\ProductRepository;

class IngredientCompatibilityController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductIngredientsIncompatibilityTemplateRepository $templateRepository,
        protected ProductIngredientsIncompatibilityRepository $incompatibilityRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(IngredientIncompatibilityTemplateDataGrid::class)->process();
        }

        return view('admin::catalog.ingredient-compatibility.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::catalog.ingredient-compatibility.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active'      => 'boolean',
        ]);

        Event::dispatch('catalog.ingredient_compatibility.create.before');

        $template = $this->templateRepository->create([
            'name'        => request()->input('name'),
            'description' => request()->input('description'),
            'active'      => request()->input('active', true),
        ]);

        Event::dispatch('catalog.ingredient_compatibility.create.after', $template);

        session()->flash('success', trans('admin::app.catalog.ingredient-compatibility.create.success'));

        return redirect()->route('admin.catalog.ingredient_compatibility.edit', $template->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $template = $this->templateRepository->with('incompatibilities.parent', 'incompatibilities.product')->findOrFail($id);

        return view('admin::catalog.ingredient-compatibility.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(int $id)
    {
        $this->validate(request(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'active'      => 'boolean',
        ]);

        Event::dispatch('catalog.ingredient_compatibility.update.before', $id);

        $template = $this->templateRepository->update([
            'name'        => request()->input('name'),
            'description' => request()->input('description'),
            'active'      => request()->input('active', true),
        ], $id);

        // Update incompatibilities
        $this->updateIncompatibilities($id);

        Event::dispatch('catalog.ingredient_compatibility.update.after', $template);

        session()->flash('success', trans('admin::app.catalog.ingredient-compatibility.edit.success'));

        return redirect()->route('admin.catalog.ingredient_compatibility.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            Event::dispatch('catalog.ingredient_compatibility.delete.before', $id);

            $this->templateRepository->delete($id);

            Event::dispatch('catalog.ingredient_compatibility.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.catalog.ingredient-compatibility.delete.success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('admin::app.catalog.ingredient-compatibility.delete.error'),
            ], 500);
        }
    }

    /**
     * Mass delete the specified resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        try {
            foreach ($indices as $index) {
                Event::dispatch('catalog.ingredient_compatibility.delete.before', $index);

                $this->templateRepository->delete($index);

                Event::dispatch('catalog.ingredient_compatibility.delete.after', $index);
            }

            return new JsonResponse([
                'message' => trans('admin::app.catalog.ingredient-compatibility.delete.mass-success'),
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search products for incompatibilities
     */
    public function searchProducts(): JsonResponse
    {
        $query = request()->input('query', '');
        $limit = request()->input('limit', 20);
        $locale = app()->getLocale();
        $channel = core()->getCurrentChannelCode();

        $queryBuilder = DB::table('product_flat')
            ->join('products', 'product_flat.product_id', '=', 'products.id')
            ->where('products.type', 'ingredient')
            ->where('product_flat.locale', $locale)
            ->where('product_flat.channel', $channel);

        // Если есть поисковый запрос, фильтруем по нему
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
     * Update incompatibilities for a template
     *
     * @param  int  $templateId
     * @return void
     */
    protected function updateIncompatibilities(int $templateId): void
    {
        $incompatibilities = request()->input('incompatibilities', []);

        // Debug: логируем полученные данные
        \Log::info('Incompatibilities data received:', [
            'count' => count($incompatibilities),
            'data' => $incompatibilities
        ]);

        // Delete existing incompatibilities
        $this->incompatibilityRepository->where('template_id', $templateId)->delete();

        // Create new incompatibilities
        foreach ($incompatibilities as $key => $incompatibility) {
            if (!empty($incompatibility['parent_id']) && !empty($incompatibility['product_id'])) {
                \Log::info("Creating incompatibility #{$key}", $incompatibility);
                
                $this->incompatibilityRepository->create([
                    'template_id' => $templateId,
                    'parent_id'   => $incompatibility['parent_id'],
                    'product_id'  => $incompatibility['product_id'],
                ]);
            } else {
                \Log::warning("Skipping incompatibility #{$key} - missing data", $incompatibility);
            }
        }
    }
}

