<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.ingredient-compatibility.edit.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.catalog.ingredient_compatibility.update', $template->id)"
        method="PUT"
        enctype="multipart/form-data"
        @submit="checkFormData"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.catalog.ingredient-compatibility.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.catalog.ingredient_compatibility.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                >
                    @lang('admin::app.catalog.ingredient-compatibility.edit.back-btn')
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.catalog.ingredient-compatibility.edit.save-btn')
                </button>
            </div>
        </div>

        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <!-- General Information -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.catalog.ingredient-compatibility.edit.general')
                    </p>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.catalog.ingredient-compatibility.edit.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="name"
                            rules="required"
                            :value="old('name', $template->name)"
                            :label="trans('admin::app.catalog.ingredient-compatibility.edit.name')"
                            :placeholder="trans('admin::app.catalog.ingredient-compatibility.edit.name')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.catalog.ingredient-compatibility.edit.description')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="description"
                            :value="old('description', $template->description)"
                            :label="trans('admin::app.catalog.ingredient-compatibility.edit.description')"
                            :placeholder="trans('admin::app.catalog.ingredient-compatibility.edit.description')"
                        />

                        <x-admin::form.control-group.error control-name="description" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.catalog.ingredient-compatibility.edit.active')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="switch"
                            name="active"
                            value="1"
                            :label="trans('admin::app.catalog.ingredient-compatibility.edit.active')"
                            :checked="old('active', $template->active)"
                        />

                        <x-admin::form.control-group.error control-name="active" />
                    </x-admin::form.control-group>
                </div>

                <!-- Incompatibilities -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.catalog.ingredient-compatibility.edit.incompatibilities')
                    </p>

                    <v-ingredient-incompatibilities :template-id="{{ $template->id }}"></v-ingredient-incompatibilities>
                </div>
            </div>
        </div>
    </x-admin::form>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-ingredient-incompatibilities-template">
            <div>
                <!-- Add Parent Ingredient Button -->
                <div class="mb-4 flex gap-2">
                    <button
                        type="button"
                        @click="openParentSearch"
                        class="secondary-button"
                    >
                        @lang('admin::app.catalog.ingredient-compatibility.edit.add-incompatibility')
                    </button>
                    
                    <!-- Debug Button (remove after testing) -->
                    <button
                        type="button"
                        @click="debugFormData"
                        class="secondary-button"
                        style="background: #f59e0b; color: white;"
                    >
                        🐛 Отладка данных
                    </button>
                </div>

                <!-- Incompatibilities List -->
                <div v-if="incompatibilityGroups.length > 0" class="space-y-4">
                    <div
                        v-for="(group, index) in incompatibilityGroups"
                        :key="index"
                        class="border rounded-lg p-4 dark:border-gray-800"
                    >
                        <!-- Parent Ingredient Header -->
                        <div class="flex justify-between items-center mb-3 pb-3 border-b dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="font-semibold text-gray-800 dark:text-white">
                                    @{{ group.parent.sku }} - @{{ group.parent.name }}
                                </div>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded dark:bg-blue-900 dark:text-blue-300">
                                    @lang('admin::app.catalog.ingredient-compatibility.edit.parent-ingredient')
                                </span>
                            </div>
                            
                            <button
                                type="button"
                                @click="removeParent(index)"
                                class="text-red-600 hover:text-red-800 dark:text-red-400"
                            >
                                <span class="icon-delete text-2xl"></span>
                            </button>
                        </div>

                        <!-- Incompatible Products -->
                        <div class="ml-6">
                            <div class="flex justify-between items-center mb-2">
                                <div class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.catalog.ingredient-compatibility.edit.incompatible-ingredients') 
                                    (@{{ group.products.length }})
                                </div>
                                
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="openProductSearch(index)"
                                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                    >
                                        + @lang('admin::app.catalog.ingredient-compatibility.edit.add-product')
                                    </button>
                                    
                                    <button
                                        type="button"
                                        @click="group.collapsed = !group.collapsed"
                                        class="text-gray-600 hover:text-gray-800 dark:text-gray-400"
                                    >
                                        <span :class="group.collapsed ? 'icon-arrow-down' : 'icon-arrow-up'" class="text-xl"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Products List (Collapsible) -->
                            <div v-show="!group.collapsed" class="space-y-2 mt-2">
                                <div
                                    v-for="(product, pIndex) in group.products"
                                    :key="pIndex"
                                    class="flex justify-between items-center p-2 bg-gray-50 rounded dark:bg-gray-800"
                                >
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                            @{{ product.sku }} - @{{ product.name }}
                                        </span>
                                    </div>
                                    
                                    <button
                                        type="button"
                                        @click="removeProduct(index, pIndex)"
                                        class="text-red-600 hover:text-red-800 text-sm dark:text-red-400"
                                    >
                                        <span class="icon-delete text-lg"></span>
                                    </button>
                                </div>

                                <!-- Empty State -->
                                <div v-if="group.products.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                                    @lang('admin::app.catalog.ingredient-compatibility.edit.no-products')
                                </div>
                            </div>
                        </div>

                        <!-- Hidden Inputs for Form Submission -->
                        <template v-for="(product, pIndex) in group.products" :key="'inputs-' + index + '-' + pIndex">
                            <input
                                type="hidden"
                                :name="'incompatibilities[' + getFlatIndex(index, pIndex) + '][parent_id]'"
                                :value="group.parent.id"
                            />
                            <input
                                type="hidden"
                                :name="'incompatibilities[' + getFlatIndex(index, pIndex) + '][product_id]'"
                                :value="product.id"
                            />
                        </template>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center text-gray-600 dark:text-gray-300 py-8">
                    @lang('admin::app.catalog.ingredient-compatibility.edit.no-incompatibilities')
                </div>

                <!-- Product Search Modal for Parent -->
                <x-admin::products.search
                    ref="parentSearchModal"
                    ::added-product-ids="addedParentIds"
                    ::search-ingredients="true"
                    @onProductAdded="addParent($event)"
                />

                <!-- Product Search Modal for Products -->
                <x-admin::products.search
                    ref="productSearchModal"
                    ::added-product-ids="addedProductIds"
                    ::search-ingredients="true"
                    @onProductAdded="addProducts($event)"
                />
            </div>
        </script>

        <script type="module">
            app.component('v-ingredient-incompatibilities', {
                template: '#v-ingredient-incompatibilities-template',

                props: ['templateId'],

                data() {
                    return {
                        incompatibilityGroups: [],
                        currentGroupIndex: null,
                    }
                },

                computed: {
                    addedParentIds() {
                        return this.incompatibilityGroups.map(group => group.parent.id);
                    },
                    
                    addedProductIds() {
                        if (this.currentGroupIndex === null) return [];
                        return this.incompatibilityGroups[this.currentGroupIndex].products.map(p => p.id);
                    }
                },

                mounted() {
                    this.loadIncompatibilities();
                },

                methods: {
                    loadIncompatibilities() {
                        @if($template->incompatibilities->count() > 0)
                            const incompatibilities = @json($template->incompatibilities->load(['parent', 'product']));
                            
                            // Group by parent_id
                            const grouped = {};
                            incompatibilities.forEach(inc => {
                                if (!grouped[inc.parent_id]) {
                                    grouped[inc.parent_id] = {
                                        parent: inc.parent,
                                        products: [],
                                        collapsed: false
                                    };
                                }
                                grouped[inc.parent_id].products.push(inc.product);
                            });
                            
                            this.incompatibilityGroups = Object.values(grouped);
                        @endif
                    },

                    openParentSearch() {
                        this.$refs.parentSearchModal.openDrawer();
                    },

                    openProductSearch(groupIndex) {
                        this.currentGroupIndex = groupIndex;
                        this.$refs.productSearchModal.openDrawer();
                    },

                    addParent(selectedProducts) {
                        selectedProducts.forEach(product => {
                            const exists = this.incompatibilityGroups.some(g => g.parent.id === product.id);
                            if (!exists) {
                                this.incompatibilityGroups.push({
                                    parent: product,
                                    products: [],
                                    collapsed: false
                                });
                            }
                        });
                    },

                    addProducts(selectedProducts) {
                        if (this.currentGroupIndex === null) return;
                        
                        const group = this.incompatibilityGroups[this.currentGroupIndex];
                        selectedProducts.forEach(product => {
                            // Не добавляем родительский продукт к самому себе
                            if (product.id === group.parent.id) return;
                            
                            const exists = group.products.some(p => p.id === product.id);
                            if (!exists) {
                                group.products.push(product);
                            }
                        });
                    },

                    removeParent(index) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.incompatibilityGroups.splice(index, 1);
                            }
                        });
                    },

                    removeProduct(groupIndex, productIndex) {
                        this.incompatibilityGroups[groupIndex].products.splice(productIndex, 1);
                    },

                    getFlatIndex(groupIndex, productIndex) {
                        let flatIndex = 0;
                        for (let i = 0; i < groupIndex; i++) {
                            flatIndex += this.incompatibilityGroups[i].products.length;
                        }
                        return flatIndex + productIndex;
                    },

                    checkFormData(event) {
                        // Debug: показываем сколько пар несовместимостей будет отправлено
                        let totalCount = 0;
                        this.incompatibilityGroups.forEach(group => {
                            totalCount += group.products.length;
                        });
                        console.log('Всего несовместимостей для сохранения:', totalCount);
                        console.log('Данные групп:', this.incompatibilityGroups);
                    },

                    debugFormData() {
                        console.log('=== DEBUG: Данные для отправки ===');
                        
                        let totalCount = 0;
                        let formData = [];
                        
                        this.incompatibilityGroups.forEach((group, groupIndex) => {
                            console.log(`Группа ${groupIndex}: ${group.parent.sku} - ${group.parent.name}`);
                            console.log(`  Продуктов: ${group.products.length}`);
                            
                            group.products.forEach((product, pIndex) => {
                                const flatIndex = this.getFlatIndex(groupIndex, pIndex);
                                formData.push({
                                    index: flatIndex,
                                    parent_id: group.parent.id,
                                    parent_name: group.parent.name,
                                    product_id: product.id,
                                    product_name: product.name
                                });
                                console.log(`    ${flatIndex}: ${product.sku} - ${product.name}`);
                                totalCount++;
                            });
                        });
                        
                        console.log('\n=== Итого ===');
                        console.log('Всего несовместимостей:', totalCount);
                        console.table(formData);
                        
                        alert(`Будет отправлено ${totalCount} несовместимостей. Смотрите детали в консоли браузера (F12)`);
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
