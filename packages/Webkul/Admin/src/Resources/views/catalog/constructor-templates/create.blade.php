<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.constructor-templates.create.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.catalog.constructor_templates.store')"
        enctype="multipart/form-data"
    >
        <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                @lang('admin::app.catalog.constructor-templates.create.title')
            </p>

            <div class="flex gap-x-2.5 items-center">
                <a
                    href="{{ route('admin.catalog.constructor_templates.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                >
                    @lang('admin::app.catalog.constructor-templates.create.back-btn')
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.catalog.constructor-templates.create.save-btn')
                </button>
            </div>
        </div>

        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
                <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                    <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                        @lang('admin::app.catalog.constructor-templates.create.general')
                    </p>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.catalog.constructor-templates.create.template-name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="template_name"
                            :value="old('template_name')"
                            rules="required"
                            :label="trans('admin::app.catalog.constructor-templates.create.template-name')"
                            :placeholder="trans('admin::app.catalog.constructor-templates.create.template-name')"
                        />

                        <x-admin::form.control-group.error control-name="template_name" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.catalog.constructor-templates.create.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="name"
                            :value="old('name')"
                            :label="trans('admin::app.catalog.constructor-templates.create.name')"
                            :placeholder="trans('admin::app.catalog.constructor-templates.create.name')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.catalog.constructor-templates.create.field-type')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="field_type"
                            :value="old('field_type', 'checkbox')"
                            rules="required"
                            :label="trans('admin::app.catalog.constructor-templates.create.field-type')"
                        >
                            <option value="checkbox">@lang('admin::app.catalog.constructor-templates.create.type-checkbox')</option>
                            <option value="radio">@lang('admin::app.catalog.constructor-templates.create.type-radio')</option>
                            <option value="list">@lang('admin::app.catalog.constructor-templates.create.type-list')</option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="field_type" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.catalog.constructor-templates.create.checked-type')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="checked_type"
                            :value="old('checked_type', 'once')"
                            rules="required"
                            :label="trans('admin::app.catalog.constructor-templates.create.checked-type')"
                        >
                            <option value="once">@lang('admin::app.catalog.constructor-templates.create.checked-once')</option>
                            <option value="multiple">@lang('admin::app.catalog.constructor-templates.create.checked-multiple')</option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="checked_type" />
                    </x-admin::form.control-group>

                    <div class="grid grid-cols-2 gap-4">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.catalog.constructor-templates.create.quantity-min')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="number"
                                name="quantity_min"
                                :value="old('quantity_min', 0)"
                                min="0"
                                :label="trans('admin::app.catalog.constructor-templates.create.quantity-min')"
                            />

                            <x-admin::form.control-group.error control-name="quantity_min" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.catalog.constructor-templates.create.quantity-max')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="number"
                                name="quantity_max"
                                :value="old('quantity_max', 0)"
                                min="0"
                                :label="trans('admin::app.catalog.constructor-templates.create.quantity-max')"
                            />

                            <x-admin::form.control-group.error control-name="quantity_max" />
                        </x-admin::form.control-group>
                    </div>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.catalog.constructor-templates.create.sort')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="number"
                            name="sort"
                            :value="old('sort', 0)"
                            min="0"
                            :label="trans('admin::app.catalog.constructor-templates.create.sort')"
                        />

                        <x-admin::form.control-group.error control-name="sort" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.catalog.constructor-templates.create.incompatibility-template')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="ingredients_incompatibilities_id"
                            :value="old('ingredients_incompatibilities_id')"
                            :label="trans('admin::app.catalog.constructor-templates.create.incompatibility-template')"
                        >
                            <option value="">@lang('admin::app.catalog.constructor-templates.create.select-template')</option>
                            @foreach($incompatibilityTemplates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="ingredients_incompatibilities_id" />
                    </x-admin::form.control-group>
                </div>

                <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                    <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                        @lang('admin::app.catalog.constructor-templates.create.options')
                    </p>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="show_title"
                                name="show_title"
                                value="1"
                                {{ old('show_title', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="show_title" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.constructor-templates.create.show-title')
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="opened_by_default"
                                name="opened_by_default"
                                value="1"
                                {{ old('opened_by_default', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="opened_by_default" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.constructor-templates.create.opened-by-default')
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="zero_price"
                                name="zero_price"
                                value="1"
                                {{ old('zero_price', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="zero_price" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.constructor-templates.create.zero-price')
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="required"
                                name="required"
                                value="1"
                                {{ old('required', false) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="required" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.constructor-templates.create.required')
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="hidden"
                                name="hidden"
                                value="1"
                                {{ old('hidden', false) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="hidden" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.constructor-templates.create.hidden')
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="double_portions"
                                name="double_portions"
                                value="1"
                                {{ old('double_portions', false) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label for="double_portions" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @lang('admin::app.catalog.constructor-templates.create.double-portions')
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="half_portions"
                            name="half_portions"
                            value="1"
                            {{ old('half_portions', false) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <label for="half_portions" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            @lang('admin::app.catalog.constructor-templates.create.half-portions')
                        </label>
                    </div>
                </div>

                <!-- Products -->
                <v-template-products></v-template-products>
            </div>
        </div>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-template-products-template"
        >
            <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex flex-col gap-1">
                        <p class="text-base text-gray-800 dark:text-white font-semibold">
                            @lang('admin::app.catalog.constructor-templates.create.products')
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.catalog.constructor-templates.create.products-count'): @{{ products.length }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="secondary-button"
                        @click="openProductSearch"
                    >
                        @lang('admin::app.catalog.constructor-templates.create.add-product')
                    </button>
                </div>

                <!-- Selected Products -->
                <div v-if="products.length" class="space-y-2">
                    <div
                        v-for="(product, productIndex) in products"
                        :key="product.id"
                        class="flex items-center justify-between p-3 border border-gray-200 rounded-lg dark:border-gray-700"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded overflow-hidden">
                                <img
                                    v-if="product.images && product.images.length"
                                    :src="product.images[0].url"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                >
                                <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <i class="icon-package text-gray-400"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">
                                    @{{ product.name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ product.sku }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.catalog.constructor-templates.create.sort'):
                                </label>
                                <input
                                    type="number"
                                    v-model="product.sort"
                                    min="0"
                                    class="w-16 rounded border border-gray-300 px-2 py-1 text-xs focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    :id="'default_' + product.id"
                                    v-model="product.default"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label :for="'default_' + product.id" class="text-xs text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.catalog.constructor-templates.create.default')
                                </label>
                            </div>

                            <button
                                type="button"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                @click="removeProduct(productIndex)"
                            >
                                <i class="icon-delete text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <p class="text-sm">@lang('admin::app.catalog.constructor-templates.create.no-products')</p>
                </div>

                <!-- Hidden Inputs for Form Submission -->
                <template v-for="(product, index) in products" :key="'input-' + product.id">
                    <input type="hidden" :name="'products[' + index + '][id]'" :value="product.id" />
                    <input type="hidden" :name="'products[' + index + '][sort]'" :value="product.sort" />
                    <input type="hidden" :name="'products[' + index + '][default]'" :value="product.default ? 1 : 0" />
                </template>

                <!-- Product Search Modal -->
                <x-admin::products.search
                    ref="productSearchModal"
                    ::added-product-ids="addedProductIds"
                    ::search-ingredients="true"
                    @onProductAdded="addProduct($event)"
                />
            </div>
        </script>

        <script type="module">
            app.component('v-template-products', {
                template: '#v-template-products-template',

                data() {
                    return {
                        products: []
                    };
                },

                computed: {
                    addedProductIds() {
                        return this.products.map(p => p.id);
                    }
                },

                methods: {
                    openProductSearch() {
                        this.$refs.productSearchModal.openDrawer();
                    },

                    addProduct(selectedProducts) {
                        selectedProducts.forEach(product => {
                            if (product && product.id && product.id > 0) {
                                const exists = this.products.some(p => p.id === product.id);
                                if (!exists) {
                                    this.products.push({
                                        ...product,
                                        default: false,
                                        sort: 0,
                                        images: product.images || []
                                    });
                                    // Снимаем выделение с добавленного продукта
                                    product.selected = false;
                                } else {
                                    this.$emitter.emit('add-flash', {
                                        type: 'warning',
                                        message: '@lang('admin::app.catalog.constructor-templates.create.product-exists')'
                                    });
                                }
                            }
                        });
                    },

                    removeProduct(index) {
                        this.products.splice(index, 1);
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>

