@pushOnce('scripts')
    <script type="text/x-template" id="v-product-category-positions-template">
        <div>
            {{--        <div class="mb-4 flex justify-between gap-5">--}}
            {{--            <div class="flex flex-col gap-2">--}}
            {{--                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">--}}
            {{--                    @lang('admin::app.settings.product_category_positions.table-hint')--}}
            {{--                </p>--}}
            {{--            </div>--}}
            {{--            <button--}}
            {{--                type="button"--}}
            {{--                class="secondary-button"--}}
            {{--                @click="addRow"--}}
            {{--            >--}}
            {{--                @lang('admin::app.settings.product_category_positions.add')--}}
            {{--            </button>--}}
            {{--        </div>--}}
            <div
                v-if="mappings.length"
                class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700"
            >
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.product_category_positions.product')
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.product_category_positions.category')
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.product_category_positions.position_type')
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.product_category_positions.position_value')
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    <tr
                        v-for="(mapping, index) in mappings"
                        :key="index"
                        class="transition-all hover:bg-gray-50 dark:hover:bg-gray-950"
                    >
                        <td class="whitespace-nowrap px-6 py-4">
                            <input
                                type="hidden"
                                :name="'mappings[' + index + '][product_id]'"
                                :value="mapping.product_id || ''"
                            />
                            <div class="flex items-center gap-2">
                                <div
                                    v-if="getProductData(mapping.product_id)"
                                    class="flex items-center gap-2"
                                >
                                    <div
                                        class="relative h-10 w-10 overflow-hidden rounded border border-gray-200 dark:border-gray-600"
                                    >
                                        <img
                                            v-if="getProductData(mapping.product_id).images && getProductData(mapping.product_id).images.length"
                                            :src="getProductData(mapping.product_id).images[0].url"
                                            class="h-full w-full object-cover"
                                        />
                                        <img
                                            v-else
                                            src="{{ bagisto_asset('images/product-placeholders/front.svg') }}"
                                            class="h-full w-full object-cover dark:invert"
                                        />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            @{{ getProductData(mapping.product_id).name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            @{{ getProductData(mapping.product_id).sku }}
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-sm text-blue-600 transition-all hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-gray-700"
                                    @click="openProductSearch(index)"
                                >
                                    @{{ mapping.product_id ? changeLabel : selectLabel }}
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <select
                                :name="'mappings[' + index + '][category_id]'"
                                v-model="mapping.category_id"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600 shadow-sm transition-all hover:border-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-400"
                            >
                                <option value="">@lang('admin::app.settings.product_category_positions.select_category')</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    @{{ (new Array(cat.depth + 1).join('\u2014 ') + (cat.depth ? ' ' : '')) + cat.name }}
                                </option>
                            </select>
                        </td>
                        <td class="px-6 py-4">
                            <select
                                :name="'mappings[' + index + '][position_type]'"
                                v-model="mapping.position_type"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600 shadow-sm transition-all hover:border-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-400"
                            >
                                <option value="top">@lang('admin::app.settings.product_category_positions.position_top')</option>
                                <option value="middle">@lang('admin::app.settings.product_category_positions.position_middle')</option>
                                <option value="bottom">@lang('admin::app.settings.product_category_positions.position_bottom')</option>
                                <option value="numeric">@lang('admin::app.settings.product_category_positions.position_numeric')</option>
                            </select>
                        </td>
                        <td class="px-6 py-4">
                            <input
                                v-if="mapping.position_type === 'numeric'"
                                type="number"
                                :name="'mappings[' + index + '][position_value]'"
                                v-model.number="mapping.position_value"
                                min="0"
                                class="block w-24 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600 shadow-sm transition-all hover:border-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:border-gray-400"
                            />
                            <input
                                v-else
                                type="hidden"
                                :name="'mappings[' + index + '][position_value]'"
                                value=""
                            />
                            <span v-if="mapping.position_type !== 'numeric'" class="text-sm text-gray-400">\u2014</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button
                                type="button"
                                class="cursor-pointer rounded p-1.5 text-lg text-red-600 transition-all hover:bg-red-50 dark:text-red-400 dark:hover:bg-gray-700"
                                @click="removeRow(index)"
                            >
                                <span class="icon-cross"></span>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-else
                class="flex flex-col items-center justify-center gap-3.5 rounded-lg border-2 border-dashed border-gray-200 px-6 py-12 dark:border-gray-700"
            >
                <img
                    src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                    class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                    alt=""
                />
                <div class="flex flex-col items-center gap-1.5">
                    <p class="text-base font-semibold text-gray-400">
                        @lang('admin::app.settings.product_category_positions.empty-title')
                    </p>
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                        @lang('admin::app.settings.product_category_positions.empty-info')
                    </p>
                </div>
                <button
                    type="button"
                    class="primary-button"
                    @click="addRow"
                >
                    @lang('admin::app.settings.product_category_positions.add')
                </button>
            </div>

            <x-admin::products.search
                ref="productSearchRef"
                ::added-product-ids="[]"
                @@onProductAdded="handleProductSelected($event)"
            />
        </div>
    </script>

    <script type="module">
        (function() {
            if (typeof app === 'undefined') {
                console.error('Vue app is not defined.');
                return;
            }
            app.component('v-product-category-positions', {
                template: '#v-product-category-positions-template',
                props: {
                    initialMappings: { type: Array, default: () => [] },
                    categories: { type: Array, default: () => [] },
                    initialProductsData: { type: Object, default: () => ({}) }
                },
                data() {
                    const mappings = Array.isArray(this.initialMappings) && this.initialMappings.length
                        ? this.initialMappings.map(m => ({
                            product_id: m.product_id || null,
                            category_id: m.category_id || '',
                            position_type: m.position_type || 'top',
                            position_value: m.position_type === 'numeric' ? (m.position_value || 0) : null
                        }))
                        : [];
                    return {
                        mappings,
                        productsData: { ...this.initialProductsData },
                        editingRowIndex: null,
                        selectLabel: '{{ __("admin::app.settings.product_category_positions.select_product") }}',
                        changeLabel: '{{ __("admin::app.settings.product_category_positions.change") }}'
                    };
                },
                methods: {
                    addRow() {
                        this.mappings.push({ product_id: null, category_id: '', position_type: 'top', position_value: 0 });
                    },
                    removeRow(index) {
                        this.$emitter.emit('open-confirm-modal', { agree: () => { this.mappings.splice(index, 1); } });
                    },
                    openProductSearch(rowIndex) {
                        this.editingRowIndex = rowIndex;
                        this.$refs.productSearchRef?.openDrawer?.();
                    },
                    handleProductSelected(products) {
                        if (this.editingRowIndex !== null && products && products.length) {
                            const product = products[0];
                            const mapping = this.mappings[this.editingRowIndex];
                            if (mapping) {
                                mapping.product_id = product.id;
                                this.productsData[product.id] = { id: product.id, name: product.name, sku: product.sku, images: product.images || [] };
                            }
                            this.editingRowIndex = null;
                        }
                    },
                    getProductData(productId) {
                        return productId ? (this.productsData[productId] || null) : null;
                    }
                }
            });
        })();
    </script>
@endPushOnce

<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.product_category_positions.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.settings.product_category_positions.store')"
        method="POST"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.product_category_positions.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.settings.locales.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.configuration.index.back-btn')
                </a>
                <button type="submit" class="primary-button">
                    @lang('admin::app.configuration.index.save-btn')
                </button>
            </div>
        </div>

        <div class="mt-7 flex flex-col gap-2">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.product_category_positions.description')
                </p>

                <v-product-category-positions
                    :initial-mappings="{{ json_encode($mappings) }}"
                    :categories="{{ json_encode($categories) }}"
                    :initial-products-data="{{ json_encode($productsData) }}"
                ></v-product-category-positions>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
