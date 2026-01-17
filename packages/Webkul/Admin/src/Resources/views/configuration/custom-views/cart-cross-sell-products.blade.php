@php
    // Получаем переменные, если они не переданы
    if (!isset($currentChannel)) {
        $currentChannel = core()->getRequestedChannel();
    }
    if (!isset($currentLocale)) {
        $currentLocale = core()->getRequestedLocale();
    }
    
    $value = system_config()->getConfigData($field->getNameKey(), $currentChannel->code, $currentLocale->code);
    $selectedProductIds = [];
    
    if (is_array($value)) {
        $selectedProductIds = $value;
    } elseif (is_string($value) && !empty($value)) {
        // Пробуем декодировать JSON
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $selectedProductIds = $decoded;
        } else {
            // Если не JSON, то это строка с запятыми (формат из CoreConfigRepository)
            $selectedProductIds = array_filter(array_map('intval', explode(',', $value)));
        }
    }
    
    $selectedProducts = [];
    if (!empty($selectedProductIds)) {
        $selectedProducts = app(\Webkul\Product\Repositories\ProductRepository::class)
            ->whereIn('id', $selectedProductIds)
            ->with('images')
            ->get()
            ->toArray();
    }
@endphp

<div class="mb-4 last:!mb-0">
    <v-cart-cross-sell-products
        :field-data="{{ json_encode($field) }}"
        :selected-products="{{ json_encode($selectedProducts) }}"
        field-name="{{ $field->getNameField() }}"
        depend-name="{{ $field->getDependFieldName() }}"
        field-title="{{ trans($field->getTitle()) }}"
        field-info="{{ trans($field->getInfo()) }}"
    ></v-cart-cross-sell-products>
</div>


@pushOnce('scripts')
<script type="text/x-template" id="v-cart-cross-sell-products-template">
    <div>
            <x-admin::form.control-group>
            <x-admin::form.control-group.label v-if="isVisible">
                @{{ fieldTitle }}
            </x-admin::form.control-group.label>

            <div v-if="isVisible" class="box-shadow rounded bg-white dark:bg-gray-900">
                <!-- Add Button -->
                <div class="mb-2.5 flex justify-between gap-5 p-4">
                    <div class="flex flex-col gap-2">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @{{ fieldTitle }}
                        </p>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @{{ fieldInfo }}
                        </p>
                    </div>

                    <div class="flex items-center gap-x-1">
                        <div
                            class="secondary-button"
                            @click="$refs.cartCrossSellProductSearch.openDrawer()"
                        >
                            @lang('admin::app.catalog.products.edit.links.add-btn')
                        </div>
                    </div>
                </div>

                <!-- Product Listing -->
                <div
                    class="grid"
                    v-if="addedProducts.length"
                >
                    <div
                        class="flex justify-between gap-2.5 border-b border-slate-300 p-4 dark:border-gray-800"
                        v-for="product in addedProducts"
                        :key="product.id"
                    >
                        <!-- Hidden Input -->
                        <input
                            type="hidden"
                            :name="fieldName + '[]'"
                            :value="product.id"
                        />

                        <!-- Information -->
                        <div class="flex gap-2.5">
                            <!-- Image -->
                            <div
                                class="relative h-[60px] max-h-[60px] w-full max-w-[60px] overflow-hidden rounded"
                                :class="{'border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert': !product.images || !product.images.length}"
                            >
                                <template v-if="!product.images || !product.images.length">
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
                                @click="remove(product)"
                            >
                                @lang('admin::app.catalog.products.edit.links.delete')
                            </p>
                        </div>
                    </div>
                </div>

                <!-- For Empty Products -->
                <div
                    class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                    v-else
                >
                    <img
                        src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                        class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                    />

                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.catalog.products.edit.links.empty-title')
                        </p>

                        <p class="text-gray-400">
                            @{{ fieldInfo }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Product Search Component -->
            <x-admin::products.search
                ref="cartCrossSellProductSearch"
                ::added-product-ids="addedProductIds"
                @onProductAdded="addSelected($event)"
            />
        </x-admin::form.control-group>
    </div>
</script>

<script type="module">
    try {
        if (typeof app === 'undefined') {
            console.error('Vue app is not defined. Component registration skipped.');
            throw new Error('Vue app is not defined');
        }
        
        app.component('v-cart-cross-sell-products', {
        template: '#v-cart-cross-sell-products-template',

        props: [
            'fieldData',
            'selectedProducts',
            'fieldName',
            'dependName',
            'fieldTitle',
            'fieldInfo',
        ],

        data() {
            let parsedField = {};
            try {
                if (this.fieldData) {
                    parsedField = JSON.parse(this.fieldData);
                }
            } catch(e) {
                console.error('Error parsing fieldData:', e);
                parsedField = {};
            }
            
            return {
                field: parsedField,
                addedProducts: Array.isArray(this.selectedProducts) ? this.selectedProducts : [],
                isVisible: true,
            };
        },

        computed: {
            addedProductIds() {
                try {
                    if (!Array.isArray(this.addedProducts)) {
                        return [];
                    }
                    return this.addedProducts.map(product => product?.id).filter(id => id !== undefined);
                } catch(e) {
                    console.error('Error in addedProductIds:', e);
                    return [];
                }
            }
        },

        mounted() {
            if (this.dependName) {
                const dependElement = document.getElementById(this.dependName);

                if (dependElement) {
                    const updateVisibility = (event) => {
                        this.isVisible = event.target.type === 'checkbox' 
                            ? event.target.checked
                            : true;
                    };

                    dependElement.addEventListener('change', updateVisibility);
                    dependElement.dispatchEvent(new Event('change'));
                }
            }
        },

        methods: {
            addSelected(selectedProducts) {
                this.addedProducts = [...this.addedProducts, ...selectedProducts];
            },

            remove(product) {
                this.$emitter.emit('open-confirm-modal', {
                    agree: () => {
                        this.addedProducts = this.addedProducts.filter(item => item.id !== product.id);
                    },
                });
            },
        }
        });
    } catch(error) {
        console.error('Error registering v-cart-cross-sell-products component:', error);
    }
</script>
@endPushOnce
