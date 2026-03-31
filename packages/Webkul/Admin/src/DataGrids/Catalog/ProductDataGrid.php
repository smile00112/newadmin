<?php

namespace Webkul\Admin\DataGrids\Catalog;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Facades\ElasticSearch;
use Webkul\DataGrid\DataGrid;
use Webkul\Product\Helpers\Product;

class ProductDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'product_id';

    /**
     * Resolve whether ingredient-only mode is requested.
     */
    protected function shouldShowIngredientsOnly(): bool
    {
        return request()->has('ingredient') && (bool) request()->input('ingredient');
    }

    /**
     * Constructor for the class.
     *
     * @return void
     */
    public function __construct(
        protected AttributeFamilyRepository $attributeFamilyRepository,
        protected AttributeRepository $attributeRepository,
        protected CategoryRepository $categoryRepository
    ) {}

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();
        $locale = app()->getLocale();
        $quotedLocale = DB::getPdo()->quote($locale);
        $manageStockAttribute = $this->attributeRepository->findOneByField('code', 'manage_stock');
        $manageStockAttributeId = $manageStockAttribute?->id;

        /**
         * Query Builder — subqueries instead of JOINs to avoid cartesian product
         */
        $queryBuilder = DB::table('product_flat')
            ->leftJoin('attribute_families as af', 'product_flat.attribute_family_id', '=', 'af.id')
            ->select(
                'product_flat.locale',
                'product_flat.channel',
                'product_flat.product_id',
                'product_flat.sku',
                'product_flat.name',
                'product_flat.type',
                'product_flat.status',
                'product_flat.price',
                'product_flat.url_key',
                'product_flat.visible_individually',
                'af.name as attribute_family',
            )
            ->addSelect(DB::raw("(SELECT path FROM {$tablePrefix}product_images WHERE product_id = {$tablePrefix}product_flat.product_id LIMIT 1) as base_image"))
            ->addSelect(DB::raw("(SELECT category_id FROM {$tablePrefix}product_categories WHERE product_id = {$tablePrefix}product_flat.product_id LIMIT 1) as category_id"))
            ->addSelect(DB::raw("(SELECT ct2.name FROM {$tablePrefix}product_categories pc2 INNER JOIN {$tablePrefix}category_translations ct2 ON pc2.category_id = ct2.category_id AND ct2.locale = {$quotedLocale} WHERE pc2.product_id = {$tablePrefix}product_flat.product_id LIMIT 1) as category_name"))
            ->addSelect(DB::raw("(SELECT COALESCE(SUM(qty), 0) FROM {$tablePrefix}product_inventories WHERE product_id = {$tablePrefix}product_flat.product_id) as quantity"))
            ->addSelect(DB::raw("(SELECT COUNT(*) FROM {$tablePrefix}product_images WHERE product_id = {$tablePrefix}product_flat.product_id) as images_count"))
            ->addSelect(DB::raw("(SELECT COALESCE(SUM(pf2.price), 0) FROM {$tablePrefix}product_constructor_group_products pcgp INNER JOIN {$tablePrefix}product_flat pf2 ON pcgp.product_id = pf2.product_id WHERE pcgp.parent_id = {$tablePrefix}product_flat.product_id) as selected_ingredients_sum"))
            ->addSelect(
                $manageStockAttributeId
                    ? DB::raw("COALESCE((SELECT boolean_value FROM {$tablePrefix}product_attribute_values WHERE product_id = {$tablePrefix}product_flat.product_id AND (channel = {$tablePrefix}product_flat.channel OR channel IS NULL) AND attribute_id = " . (int) $manageStockAttributeId . " LIMIT 1), 0) as manage_stock")
                    : DB::raw('0 as manage_stock')
            )
            ->where('product_flat.locale', $locale)
            ->groupBy('product_flat.product_id');

        $this->addFilter('product_id', 'product_flat.product_id');
        $this->addFilter('channel', 'product_flat.channel');
        $this->addFilter('locale', 'product_flat.locale');
        $this->addFilter('name', 'product_flat.name');
        $this->addFilter('type', 'product_flat.type');
        $this->addFilter('status', 'product_flat.status');
        $this->addFilter('attribute_family', 'af.id');

        return $queryBuilder;
    }

    /**
     * Process requested filters — override to handle category_name via subquery.
     */
    protected function processRequestedFilters(array $requestedFilters)
    {
        if (isset($requestedFilters['category_name'])) {
            $categoryIds = (array) $requestedFilters['category_name'];

            $this->queryBuilder->whereIn('product_flat.product_id', function ($sub) use ($categoryIds) {
                $sub->select('product_id')
                    ->from('product_categories')
                    ->whereIn('category_id', $categoryIds);
            });

            unset($requestedFilters['category_name']);
        }

        parent::processRequestedFilters($requestedFilters);
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $channels = core()->getAllChannels();

        if ($channels->count() > 1) {
            $this->addColumn([
                'index'              => 'channel',
                'label'              => trans('admin::app.catalog.products.index.datagrid.channel'),
                'type'               => 'string',
                'filterable'         => true,
                'filterable_type'    => 'dropdown',
                'filterable_options' => collect($channels)
                    ->map(fn ($channel) => ['label' => $channel->name, 'value' => $channel->code])
                    ->values()
                    ->toArray(),
                'sortable'   => true,
                'visibility' => false,
            ]);
        }

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.catalog.products.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'sku',
            'label'      => trans('admin::app.catalog.products.index.datagrid.sku'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'attribute_family',
            'label'              => trans('admin::app.catalog.products.index.datagrid.attribute-family'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => $this->attributeFamilyRepository->all(['name as label', 'id as value'])->toArray(),
        ]);

        $this->addColumn([
            'index'      => 'base_image',
            'label'      => trans('admin::app.catalog.products.index.datagrid.image'),
            'type'       => 'string',
            'exportable' => false,
            'closure'    => function ($row) {
                if (! $row->base_image) {
                    return;
                }

                return Storage::url($row->base_image);
            },
        ]);

        $this->addColumn([
            'index'      => 'price',
            'label'      => trans('admin::app.catalog.products.index.datagrid.price'),
            'type'       => 'decimal',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'quantity',
            'label'      => trans('admin::app.catalog.products.index.datagrid.qty'),
            'type'       => 'integer',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'product_id',
            'label'      => trans('admin::app.catalog.products.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('admin::app.catalog.products.index.datagrid.status'),
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('admin::app.catalog.products.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('admin::app.catalog.products.index.datagrid.disable'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'category_name',
            'label'              => trans('admin::app.catalog.products.index.datagrid.category'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => $this->categoryRepository
                ->all()
                ->map(fn ($cat) => [
                    'label' => $cat->translate(app()->getLocale())?->name ?? $cat->name,
                    'value' => (string) $cat->id,
                ])
                ->values()
                ->toArray(),
        ]);

        $this->addColumn([
            'index'              => 'type',
            'label'              => trans('admin::app.catalog.products.index.datagrid.type'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => collect(config('product_types'))
                ->map(fn ($type) => ['label' => trans($type['name']), 'value' => $type['key']])
                ->values()
                ->toArray(),
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('catalog.products.copy')) {
            $this->addAction([
                'icon'   => 'icon-copy',
                'title'  => trans('admin::app.catalog.products.index.datagrid.copy'),
                'method' => 'POST',
                'url'    => function ($row) {
                    return route('admin.catalog.products.copy', $row->product_id);
                },
            ]);
        }

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => trans('admin::app.catalog.products.index.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.catalog.products.delete', $row->product_id);
            },
        ]);

        if (bouncer()->hasPermission('catalog.products.edit')) {
            $this->addAction([
                'icon'   => 'icon-sort-right',
                'title'  => trans('admin::app.catalog.products.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    $filteredChannel = request()->input('filters.channel')[0] ?? null;

                    return route('admin.catalog.products.edit', [
                        'id'      => $row->product_id,
                        'channel' => $filteredChannel,
                    ]);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('catalog.products.delete')) {
            $this->addMassAction([
                'title'  => trans('admin::app.catalog.products.index.datagrid.delete'),
                'url'    => route('admin.catalog.products.mass_delete'),
                'method' => 'POST',
            ]);
        }

        if (bouncer()->hasPermission('catalog.products.edit')) {
            $this->addMassAction([
                'title'   => trans('admin::app.catalog.products.index.datagrid.update-status'),
                'url'     => route('admin.catalog.products.mass_update'),
                'method'  => 'POST',
                'options' => [
                    [
                        'label' => trans('admin::app.catalog.products.index.datagrid.active'),
                        'value' => 1,
                    ],
                    [
                        'label' => trans('admin::app.catalog.products.index.datagrid.disable'),
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }

    /**
     * Process request.
     */
    protected function processRequest(): void
    {
        if (
            core()->getConfigData('catalog.products.search.engine') != 'elastic'
            || core()->getConfigData('catalog.products.search.admin_mode') != 'elastic'
        ) {
            parent::processRequest();

            return;
        }

        /**
         * Store all request parameters in this variable; avoid using direct request helpers afterward.
         */
        $params = $this->validatedRequest();

        if (isset($params['export']) && (bool) $params['export']) {
            parent::processRequest();

            return;
        }

        $this->dispatchEvent('process_request.before', $this);

        $pagination = $params['pagination'];

        $channelCodes = request()->input('filters.channel') ?? core()->getAllChannels()->pluck('code')->toArray();

        $indexNames = collect($channelCodes)->map(function ($channelCode) {
            return Product::formatElasticSearchIndexName($channelCode, app()->getLocale());
        })->toArray();

        $results = Elasticsearch::search([
            'index' => $indexNames,
            'body'  => [
                'from'             => ($pagination['page'] * $pagination['per_page']) - $pagination['per_page'],
                'size'             => $pagination['per_page'],
                'stored_fields'    => [],
                'query'            => [
                    'bool' => $this->getElasticFilters($params['filters'] ?? []) ?: new \stdClass,
                ],
                'sort'             => $this->getElasticSort($params['sort'] ?? []),
                'track_total_hits' => true,
            ],
        ]);

        $ids = collect($results['hits']['hits'])->pluck('_id')->toArray();

        if ($this->shouldShowIngredientsOnly()) {
            $this->queryBuilder->where('product_flat.type', 'ingredient');
        } else {
            $this->queryBuilder->where('product_flat.type', '<>', 'ingredient');
        }

        $this->queryBuilder
            ->whereIn('product_flat.product_id', $ids);

        if ($ids) {
            $this->queryBuilder
                ->orderBy(DB::raw('FIELD('.DB::getTablePrefix().'product_flat.product_id, '.implode(',', $ids).')'));
        }

        $total = $results['hits']['total']['value'];

        $this->paginator = new LengthAwarePaginator(
            $total ? $this->queryBuilder->get() : [],
            $total,
            $pagination['per_page'],
            $pagination['page'],
            [
                'path'  => request()->url(),
                'query' => [],
            ]
        );

        $this->dispatchEvent('process_request.after', $this);
    }

    /**
     * Process request.
     */
    protected function getElasticFilters($params): array
    {
        $filters = [];

        foreach ($params as $attribute => $value) {
            if (in_array($attribute, ['channel', 'locale'])) {
                continue;
            }

            if ($attribute == 'all') {
                $attribute = 'name';
            }

            $filters['filter'][] = $this->getFilterValue($attribute, $value);
        }

        if ($this->shouldShowIngredientsOnly()) {
            $filters['filter'][] = [
                'term' => [
                    'type.keyword' => 'ingredient',
                ],
            ];
        } else {
            $filters['must_not'][] = [
                'term' => [
                    'type.keyword' => 'ingredient',
                ],
            ];
        }

        return $filters;
    }

    /**
     * Return applied filters
     */
    public function getFilterValue(mixed $attribute, mixed $values): array
    {
        switch ($attribute) {
            case 'product_id':
                return [
                    'terms' => [
                        'id' => $values,
                    ],
                ];

            case 'attribute_family':
                return [
                    'terms' => [
                        'attribute_family_id' => $values,
                    ],
                ];

            case 'sku':
            case 'name':
                $filters = [];

                foreach ($values as $value) {
                    $filters['bool']['should'][] = [
                        'match_phrase_prefix' => [
                            $attribute => $value,
                        ],
                    ];
                }

                return $filters;

            default:
                return [
                    'terms' => [
                        $attribute => $values,
                    ],
                ];
        }
    }

    /**
     * Process request.
     */
    protected function getElasticSort($params): array
    {
        $sort = $params['column'] ?? $this->primaryColumn;

        if ($sort == 'type') {
            $sort .= '.keyword';
        }

        if ($sort == 'name') {
            $sort .= '.keyword';
        }

        if ($sort == 'attribute_family') {
            $sort .= '_id';
        }

        if ($sort == 'product_id') {
            $sort = 'id';
        }

        return [
            $sort => [
                'order' => $params['order'] ?? $this->sortOrder,
            ],
        ];
    }
}
