{!! view_render_event('bagisto.admin.catalog.product.edit.form.links.before', ['product' => $product]) !!}

<v-product-links></v-product-links>

{!! view_render_event('bagisto.admin.catalog.product.edit.form.links.after', ['product' => $product]) !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-product-links-template"
    >
        <div class="grid gap-2.5">
            <!-- Panel -->
            <div
                class="box-shadow relative rounded bg-white dark:bg-gray-900"
                v-for="type in availableTypes"
            >
                <div class="mb-2.5 flex justify-between gap-5 p-4">
                    <div class="flex flex-col gap-2">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @{{ type.title }}
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @{{ type.info }}
                        </p>
                    </div>

                    <!-- Add Button -->
                    <div class="flex items-center gap-x-1">
                        <div
                            class="secondary-button"
                            @click="selectedType = type.key; $refs.productSearch.openDrawer()"
                        >
                            @lang('admin::app.catalog.products.edit.links.add-btn')
                        </div>
                    </div>
                </div>

                <!-- Product Listing -->
                <!-- Draggable for drinks -->
                <draggable
                    v-if="type.key === 'drinks' && addedProducts[type.key].length"
                    ghost-class="draggable-ghost"
                    handle=".icon-drag"
                    v-bind="{animation: 200}"
                    :list="addedProducts[type.key]"
                    item-key="id"
                    @end="updateDrinksSortOrder"
                >
                    <template #item="{ element: product, index }">
                        <div
                            class="flex justify-between gap-2.5 border-b border-slate-300 p-4 dark:border-gray-800"
                            :class="{'bg-blue-50 dark:bg-blue-900/20': product.pivot && product.pivot.default}"
                        >
                        <!-- Hidden Inputs for drinks -->
                        <input
                            type="hidden"
                            :name="'drinks[' + product.id + '][id]'"
                            :value="product.id"
                        />
                        <input
                            type="hidden"
                            :name="'drinks[' + product.id + '][sort]'"
                            :value="product.pivot ? product.pivot.sort : index"
                        />
                        <input
                            type="hidden"
                            :name="'drinks[' + product.id + '][default]'"
                            :value="product.pivot && product.pivot.default ? 1 : 0"
                        />

                        <!-- Information -->
                        <div class="flex gap-2.5">
                            <!-- Drag Icon -->
                            <i
                                class="icon-drag cursor-grab text-xl text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white"
                            ></i>

                            <!-- Image -->
                            <div
                                class="relative h-[60px] max-h-[60px] w-full max-w-[60px] overflow-hidden rounded"
                                :class="{'border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert': ! product.images.length}"
                            >
                                <template v-if="! product.images.length">
                                    <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}">

                                    <p class="absolute bottom-1.5 w-full text-center text-[6px] font-semibold text-gray-400">
                                        @lang('admin::app.catalog.products.edit.links.image-placeholder')
                                    </p>
                                </template>

                                <template v-else>
                                    <img :src="product.images[0].url">
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="grid place-content-start gap-1.5">
                                <div class="flex items-center gap-2">
                                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                                        @{{ product.name }}
                                    </p>
                                    <span
                                        v-if="product.pivot && product.pivot.default"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                    >
                                        @lang('admin::app.catalog.products.edit.links.drinks.default')
                                    </span>
                                </div>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ "@lang('admin::app.catalog.products.edit.links.sku')".replace(':sku', product.sku) }}
                                </p>

                                <!-- Sort and Default fields for drinks -->
                                <div
                                    class="flex items-center gap-4 mt-2"
                                >
                                    <div class="flex items-center gap-2" style="display: none">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            @lang('admin::app.catalog.products.edit.links.drinks.sort')
                                        </label>
                                        <input
                                            type="number"
                                            class="w-20 px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                                            :value="product.pivot ? product.pivot.sort : 0"
                                            @input="updateDrinkSort(product, $event.target.value)"
                                            min="0"
                                        />
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            :checked="product.pivot && product.pivot.default"
                                            @change="updateDrinkDefault(product, $event.target.checked)"
                                        />
                                        <label class="block text-xs text-gray-900 dark:text-gray-300 cursor-pointer">
                                            @lang('admin::app.catalog.products.edit.links.drinks.default-label')
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="grid place-content-start gap-1 text-right">
                            <p class="font-semibold text-gray-800 dark:text-white">
                                @{{ $admin.formatPrice(product.price) }}
                            </p>

                            <p
                                class="cursor-pointer text-red-600 transition-all hover:underline"
                                @click="remove('drinks', product)"
                            >
                                @lang('admin::app.catalog.products.edit.links.delete')
                            </p>
                        </div>
                    </div>
                    </template>
                </draggable>

                <!-- Regular list for other types -->
                <div
                    v-if="type.key !== 'drinks' && addedProducts[type.key].length"
                    class="grid"
                >
                    <div
                        class="flex justify-between gap-2.5 border-b border-slate-300 p-4 dark:border-gray-800"
                        v-for="product in addedProducts[type.key]"
                    >
                        <!-- Hidden Inputs -->
                        <input
                            type="hidden"
                            :name="type.key + '[]'"
                            :value="product.id"
                        />

                        <!-- Information -->
                        <div class="flex gap-2.5">
                            <!-- Image -->
                            <div
                                class="relative h-[60px] max-h-[60px] w-full max-w-[60px] overflow-hidden rounded"
                                :class="{'border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert': ! product.images.length}"
                            >
                                <template v-if="! product.images.length">
                                    <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}">

                                    <p class="absolute bottom-1.5 w-full text-center text-[6px] font-semibold text-gray-400">
                                        @lang('admin::app.catalog.products.edit.links.image-placeholder')
                                    </p>
                                </template>

                                <template v-else>
                                    <img :src="product.images[0].url">
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="grid place-content-start gap-1.5">
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @{{ product.name }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ "@lang('admin::app.catalog.products.edit.links.sku')".replace(':sku', product.sku) }}
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="grid place-content-start gap-1 text-right">
                            <p class="font-semibold text-gray-800 dark:text-white">
                                @{{ $admin.formatPrice(product.price) }}
                            </p>

                            <p
                                class="cursor-pointer text-red-600 transition-all hover:underline"
                                @click="remove(type.key, product)"
                            >
                                @lang('admin::app.catalog.products.edit.links.delete')
                            </p>
                        </div>
                    </div>
                </div>

                <!-- For Empty Variations -->
                <div
                    class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                    v-else
                >
                    <!-- Placeholder Image -->
                    <img
                        src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                        class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                    />

                    <!-- Add Variants Information -->
                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.catalog.products.edit.links.empty-title')
                        </p>

                        <p class="text-gray-400">
                            @{{ type.empty_info }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Product Search Blade Component -->
            <x-admin::products.search
                ref="productSearch"
                ::added-product-ids="addedProductIds"
                @onProductAdded="addSelected($event)"
            />
        </div>
    </script>

    <script type="module">
        app.component('v-product-links', {
            template: '#v-product-links-template',

            data() {
                return {
                    currentProduct: @json($product),

                    selectedType: 'related_products',

                    types: [
                        {
                            key: 'related_products',
                            title: `@lang('admin::app.catalog.products.edit.links.related-products.title')`,
                            info: `@lang('admin::app.catalog.products.edit.links.related-products.info')`,
                            empty_info: `@lang('admin::app.catalog.products.edit.links.related-products.empty-info')`,
                        }, {
                            key: 'up_sells',
                            title: `@lang('admin::app.catalog.products.edit.links.up-sells.title')`,
                            info: `@lang('admin::app.catalog.products.edit.links.up-sells.info')`,
                            empty_info: `@lang('admin::app.catalog.products.edit.links.up-sells.empty-info')`,
                        }, {
                            key: 'cross_sells',
                            title: `@lang('admin::app.catalog.products.edit.links.cross-sells.title')`,
                            info: `@lang('admin::app.catalog.products.edit.links.cross-sells.info')`,
                            empty_info: `@lang('admin::app.catalog.products.edit.links.cross-sells.empty-info')`,
                        }, {
                            key: 'drinks',
                            title: `@lang('admin::app.catalog.products.edit.links.drinks.title')`,
                            info: `@lang('admin::app.catalog.products.edit.links.drinks.info')`,
                            empty_info: `@lang('admin::app.catalog.products.edit.links.drinks.empty-info')`,
                        }
                    ],

                    addedProducts: {
                        'up_sells': @json($product->up_sells()->with('images')->get()),

                        'cross_sells': @json($product->cross_sells()->with('images')->get()),

                        'related_products': @json($product->related_products()->with('images')->get()),

                        'drinks': @json($product->drinks()->with('images')->orderByPivot('sort')->get())
                    },
                }
            },

            computed: {
                addedProductIds() {
                    let productIds = this.addedProducts[this.selectedType].map(product => product.id);

                    productIds.push(this.currentProduct.id);

                    return productIds;
                },

                availableTypes() {
                    const allowedTypes = ['simple', 'constructor', 'configurable', 'grouped', 'bundle'];
                    if (!allowedTypes.includes(this.currentProduct.type)) {
                        return this.types.filter(type => type.key !== 'drinks');
                    }
                    return this.types;
                }
            },

            methods: {
                addSelected(selectedProducts) {
                    if (this.selectedType === 'drinks') {
                        // For drinks, add pivot data with correct sort order
                        const currentLength = this.addedProducts[this.selectedType].length;
                        const drinksWithPivot = selectedProducts.map((product, index) => ({
                            ...product,
                            pivot: {
                                sort: currentLength + index,
                                default: false
                            }
                        }));
                        this.addedProducts[this.selectedType] = [...this.addedProducts[this.selectedType], ...drinksWithPivot];
                    } else {
                        this.addedProducts[this.selectedType] = [...this.addedProducts[this.selectedType], ...selectedProducts];
                    }
                },

                remove(type, product) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            this.addedProducts[type] = this.addedProducts[type].filter(item => item.id !== product.id);
                        },
                    });
                },

                updateDrinkSort(product, value) {
                    const sortValue = parseInt(value) || 0;
                    if (product.pivot) {
                        product.pivot.sort = sortValue;
                    } else {
                        product.pivot = { sort: sortValue, default: false };
                    }
                },

                updateDrinksSortOrder() {
                    // Update sort values based on new order after drag and drop
                    this.addedProducts.drinks.forEach((product, index) => {
                        if (!product.pivot) {
                            product.pivot = { sort: index, default: false };
                        } else {
                            product.pivot.sort = index;
                        }
                    });
                },

                updateDrinkDefault(product, checked) {
                    if (product.pivot) {
                        product.pivot.default = checked;
                    } else {
                        product.pivot = { sort: 0, default: checked };
                    }
                },

                totalQty(product) {
                    let qty = 0;

                    product.inventories.forEach(function (inventory) {
                        qty += inventory.qty;
                    });

                    return qty;
                }
            }
        });
    </script>
@endPushOnce
