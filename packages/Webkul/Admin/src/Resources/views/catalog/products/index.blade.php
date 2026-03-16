<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.products.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 15px rgba(245,158,11,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.catalog.products.index.title')
                </p>
                <p class="text-xs text-gray-400">Каталог товаров</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            <!-- Export Modal -->
            <x-admin::datagrid.export :src="route('admin.catalog.products.index')" />

            {!! view_render_event('bagisto.admin.catalog.products.create.before') !!}

            @if (bouncer()->hasPermission('catalog.products.create'))
                <v-create-product-form>
                    <button
                        type="button"
                        class="primary-button"
                    >
                        @lang('admin::app.catalog.products.index.create-btn')
                    </button>
                </v-create-product-form>
            @endif

            {!! view_render_event('bagisto.admin.catalog.products.create.after') !!}
        </div>
    </div>

    <!-- Product Slide-Out Drawer (SPA) -->
    <v-product-drawer></v-product-drawer>

    {!! view_render_event('bagisto.admin.catalog.products.list.before') !!}

    <!-- Datagrid -->
    @php
        $datagridSrc = route('admin.catalog.products.index');
        if (request('category')) {
            $datagridSrc .= '?' . http_build_query(['filters' => ['category_name' => [request('category')]]]);
        }
    @endphp
    <x-admin::datagrid
        :src="$datagridSrc"
        :isMultiRow="true"
    >
        <!-- Datagrid Header -->
        @php
            $hasPermission = bouncer()->hasPermission('catalog.products.edit') || bouncer()->hasPermission('catalog.products.delete');
        @endphp

        <template #header="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>

            <template v-else>
                <div class="row grid gap-2 md:grid-cols-[2fr_1fr_1fr] grid-rows-1 items-center border-b px-4 py-2.5 dark:border-gray-800">
                    <div
                        class="flex select-none items-center gap-2.5"
                        v-for="(columnGroup, index) in [['name', 'sku', 'attribute_family'], ['base_image', 'price', 'quantity', 'product_id'], ['status', 'category_name', 'type']]"
                    >
                        @if ($hasPermission)
                            <label
                                class="flex w-max cursor-pointer select-none items-center gap-1"
                                for="mass_action_select_all_records"
                                v-if="! index"
                            >
                                <input
                                    type="checkbox"
                                    name="mass_action_select_all_records"
                                    id="mass_action_select_all_records"
                                    class="peer hidden"
                                    :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                    @change="selectAll"
                                >

                                <span
                                    class="icon-uncheckbox cursor-pointer rounded-md text-2xl"
                                    :class="[
                                        applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checked peer-checked:text-blue-600' : (
                                            applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-partial peer-checked:text-blue-600' : ''
                                        ),
                                    ]"
                                >
                                </span>
                            </label>
                        @endif

                        <p class="text-gray-600 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                        }"
                                        @click="
                                            available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)): {}
                                        "
                                    >
                                        @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                    </span>
                                </template>
                            </span>

                            <i
                                class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5"
                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                v-if="columnGroup.includes(applied.sort.column)"
                            ></i>
                        </p>
                    </div>
                </div>
            </template>
        </template>

        <template #body="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>

            <template v-else>
                <div
                    class="row border-b px-2 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950 sm:px-4 md:grid md:grid-cols-[2fr_1fr_1fr] md:grid-rows-1 md:gap-1.5"
                    v-for="record in available.records"
                    style="cursor: pointer;"
                    @click="$emitter.emit('open-product-drawer', record)"
                    @mouseenter="$event.currentTarget.style.boxShadow='inset 3px 0 0 #f59e0b'"
                    @mouseleave="$event.currentTarget.style.boxShadow='none'"
                >
                    <!-- Mobile Layout -->
                    <div class="block space-y-3 md:hidden">
                        <!-- Header Row with Checkbox, Name and Actions -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5">
                                @if ($hasPermission)
                                    <input
                                        type="checkbox"
                                        :name="`mass_action_select_record_${record.product_id}`"
                                        :id="`mass_action_select_record_${record.product_id}`"
                                        :value="record.product_id"
                                        class="peer hidden"
                                        v-model="applied.massActions.indices"
                                        @click.stop
                                    >

                                    <label
                                        class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                                        :for="`mass_action_select_record_${record.product_id}`"
                                        @click.stop
                                    ></label>
                                @endif

                                <div class="relative flex-shrink-0">
                                    <template v-if="record.base_image">
                                        <img
                                            class="h-12 w-12 rounded object-cover sm:h-16 sm:w-16"
                                            :src='record.base_image'
                                        />

                                        <span class="absolute -bottom-1 -right-1 rounded-full bg-darkPink px-1 text-xs font-bold leading-normal text-white">
                                            @{{ record.images_count }}
                                        </span>
                                    </template>

                                    <template v-else>
                                        <div class="relative h-12 w-12 rounded border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert sm:h-16 sm:w-16">
                                            <img src="{{ bagisto_asset('images/product-placeholders/front.svg')}}" class="h-full w-full object-cover">

                                            <p class="absolute bottom-0 w-full text-center text-[6px] font-semibold text-gray-400">
                                                @lang('admin::app.catalog.products.index.datagrid.product-image')
                                            </p>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex flex-col gap-1 flex-1">
                                    <a class="break-all text-sm font-semibold text-gray-800 dark:text-white sm:text-base cursor-pointer"
                                       :href="`/admin/catalog/products/edit/${record.product_id}`"
                                       @click.stop
                                    >
                                        @{{ record.name }}
                                    </a>

                                    <p class="text-xs text-gray-600 dark:text-gray-300 sm:text-sm">
                                        @{{ "@lang('admin::app.catalog.products.index.datagrid.id-value')".replace(':id', record.product_id) }}
                                    </p>

                                    <p class="text-xs text-gray-600 dark:text-gray-300 sm:text-sm">
                                        @{{ "@lang('admin::app.catalog.products.index.datagrid.sku-value')".replace(':sku', record.sku) }}
                                    </p>

                                    <p class="text-xs text-gray-600 dark:text-gray-300 sm:text-sm">
                                        @{{ "@lang('admin::app.catalog.products.index.datagrid.attribute-family-value')".replace(':attribute_family', record.attribute_family) }}
                                    </p>

                                    <p class="text-xs text-gray-600 dark:text-gray-300 sm:text-sm">
                                        @{{ record.category_name ?? 'N/A' }}
                                    </p>

                                    <p class="text-xs text-gray-600 dark:text-gray-300 sm:text-sm">
                                        @{{ record.type }}
                                    </p>

                                    <p class="text-sm font-semibold text-gray-800 dark:text-white sm:text-base">
                                        @{{ $admin.formatPrice(record.price) }}
                                    </p>

                                    <div>
                                        <div v-if="['configurable', 'bundle', 'grouped' , 'booking'].includes(record.type)">
                                            <p class="text-xs text-gray-600 dark:text-gray-300 sm:text-sm">
                                                <span class="text-red-600">N/A</span>
                                            </p>
                                        </div>

                                        <div v-else>
                                            <p
                                                class="text-xs text-gray-600 dark:text-gray-300 sm:text-sm"
                                                v-if="!record.manage_stock"
                                            >
                                                <span class="text-green-600">В наличии</span>
                                            </p>

                                            <p
                                                class="text-xs text-gray-600 dark:text-gray-300 sm:text-sm"
                                                v-else-if="record.quantity > 0"
                                            >
                                                <span class="text-green-600">
                                                    @{{ "@lang('admin::app.catalog.products.index.datagrid.qty-value')".replace(':qty', record.quantity) }}
                                                </span>
                                            </p>

                                            <p
                                                class="text-xs text-gray-600 dark:text-gray-300 sm:text-sm"
                                                v-else
                                            >
                                                <span class="text-red-600">
                                                    @lang('admin::app.catalog.products.index.datagrid.out-of-stock')
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <p :class="[record.status ? 'label-active': 'label-info']">
                                        @{{ record.status ? "@lang('admin::app.catalog.products.index.datagrid.active')" : "@lang('admin::app.catalog.products.index.datagrid.disable')" }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1">
                                <span
                                    class="cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                    :class="action.icon"
                                    v-text="! action.icon ? action.title : ''"
                                    v-for="action in record.actions"
                                    @click.stop="performAction(action)"
                                >
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Layout (Hidden on Mobile) -->
                    <div class="hidden md:contents">
                        <!-- Name, SKU, Attribute Family Columns -->
                        <div class="flex gap-2.5">
                            @if ($hasPermission)
                                <input
                                    type="checkbox"
                                    :name="`mass_action_select_record_${record.product_id}`"
                                    :id="`mass_action_select_record_${record.product_id}`"
                                    :value="record.product_id"
                                    class="peer hidden"
                                    v-model="applied.massActions.indices"
                                    @click.stop
                                >

                                <label
                                    class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                                    :for="`mass_action_select_record_${record.product_id}`"
                                    @click.stop
                                ></label>
                            @endif

                            <div class="flex flex-col gap-1.5">
                                <a class="break-all text-base font-semibold text-gray-800 dark:text-white cursor-pointer"
                                   :href="`/admin/catalog/products/edit/${record.product_id}`"
                                   @click.stop
                                >
                                    @{{ record.name }}
                                </a>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ "@lang('admin::app.catalog.products.index.datagrid.sku-value')".replace(':sku', record.sku) }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ "@lang('admin::app.catalog.products.index.datagrid.attribute-family-value')".replace(':attribute_family', record.attribute_family) }}
                                </p>
                            </div>
                        </div>

                        <!-- Image, Price, Id, Stock Columns -->
                        <div class="flex gap-1.5">
                            <div class="relative">
                                <template v-if="record.base_image">
                                    <img
                                        class="max-h-[65px] min-h-[65px] min-w-[65px] max-w-[65px] rounded"
                                        :src='record.base_image'
                                    />

                                    <span class="absolute bottom-px rounded-full bg-darkPink px-1.5 text-xs font-bold leading-normal text-white ltr:left-px rtl:right-px">
                                        @{{ record.images_count }}
                                    </span>
                                </template>

                                <template v-else>
                                    <div class="relative h-[60px] max-h-[60px] w-full max-w-[60px] rounded border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert">
                                        <img src="{{ bagisto_asset('images/product-placeholders/front.svg')}}">

                                        <p class="absolute bottom-1.5 w-full text-center text-[6px] font-semibold text-gray-400">
                                            @lang('admin::app.catalog.products.index.datagrid.product-image')
                                        </p>
                                    </div>
                                </template>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <p class="text-base font-semibold" style="color: #059669;">
{{--                                    TODO add have price_from property--}}
                                    @{{ $admin.formatPrice(record.price, (record.type === 'constructor' || record.type === 'grouped'), record.selected_ingredients_sum*1, record) }}
                                </p>

                                <!-- Parent Product Quantity -->
                                <div v-if="['configurable', 'bundle', 'grouped' , 'booking'].includes(record.type)">
                                    <p class="text-gray-600 dark:text-gray-300">
                                        <span class="text-red-600">N/A</span>
                                    </p>
                                </div>

                                <div v-else>
                                    <p
                                        class="text-gray-600 dark:text-gray-300"
                                        v-if="!record.manage_stock"
                                    >
                                        <span class="text-green-600">В наличии</span>
                                    </p>

                                    <p
                                        class="text-gray-600 dark:text-gray-300"
                                        v-else-if="record.quantity > 0"
                                    >
                                        <span class="text-green-600">
                                            @{{ "@lang('admin::app.catalog.products.index.datagrid.qty-value')".replace(':qty', record.quantity) }}
                                        </span>
                                    </p>

                                    <p
                                        class="text-gray-600 dark:text-gray-300"
                                        v-else
                                    >
                                        <span class="text-red-600">
                                            @lang('admin::app.catalog.products.index.datagrid.out-of-stock')
                                        </span>
                                    </p>
                                </div>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ "@lang('admin::app.catalog.products.index.datagrid.id-value')".replace(':id', record.product_id) }}
                                </p>
                            </div>
                        </div>

                        <!-- Status, Category, Type Columns with Inline Editing -->
                        <div class="flex items-center justify-between gap-x-4">
                            <div class="flex flex-col gap-1.5">
                                <!-- Inline Status Toggle -->
                                <v-inline-product-status
                                    :product-id="record.product_id"
                                    :initial-status="record.status"
                                    @click.stop
                                ></v-inline-product-status>

                                <!-- Inline Category Editor -->
                                <v-inline-product-category
                                    :product-id="record.product_id"
                                    :initial-category="record.category_name"
                                    @click.stop
                                ></v-inline-product-category>

                                <!-- Inline Type Editor -->
                                <v-inline-product-type
                                    :product-id="record.product_id"
                                    :initial-type="record.type"
                                    @click.stop
                                ></v-inline-product-type>
                            </div>

                            <p
                                class="flex items-center gap-1"
                                v-if="available.actions.length"
                            >
                                <span
                                    v-for="action in record.actions"
                                    @click.stop="performAction(action)"
                                    style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; transition: all 0.15s;"
                                    @mouseenter="$event.currentTarget.style.background='#ede9fe'"
                                    @mouseleave="$event.currentTarget.style.background='transparent'"
                                >
                                    <i
                                        :class="action.icon"
                                        style="font-size: 20px; color: #7c3aed;"
                                        v-if="action.icon"
                                    ></i>
                                    <span v-else style="font-size: 12px; color: #7c3aed; font-weight: 600;">@{{ action.title }}</span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    {!! view_render_event('bagisto.admin.catalog.products.list.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-create-product-form-template"
        >
            <div>
                <!-- Product Create Button -->
                @if (bouncer()->hasPermission('catalog.products.create'))
                    <button
                        type="button"
                        class="primary-button"
                        @click="createAndOpen"
                        :disabled="isCreating"
                        :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': isCreating }"
                    >
                        <span v-if="isCreating" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Создание...
                        </span>
                        <span v-else>@lang('admin::app.catalog.products.index.create-btn')</span>
                    </button>
                @endif

                <!-- Configurable Attributes Modal (only for configurable types) -->
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, submitConfigurable)">
                        <x-admin::modal ref="productCreateModal">
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @lang('admin::app.catalog.products.index.create.configurable-attributes')
                                </p>
                            </x-slot>

                            <x-slot:content>
                                <div
                                    class="mb-2.5"
                                    v-for="attribute in attributes"
                                >
                                    <label
                                        class="block text-xs font-medium leading-6 text-gray-800 dark:text-white"
                                        v-text="attribute.name"
                                    ></label>

                                    <div class="flex min-h-[38px] flex-wrap gap-1 rounded-md border p-1.5 dark:border-gray-800">
                                        <p
                                            class="flex items-center rounded bg-gray-600 px-2 py-1 font-semibold text-white"
                                            v-for="option in attribute.options"
                                        >
                                            @{{ option.name }}
                                            <span
                                                class="icon-cross cursor-pointer text-lg text-white ltr:ml-1.5 rtl:mr-1.5"
                                                @click="removeOption(option)"
                                            ></span>
                                        </p>
                                    </div>
                                </div>
                            </x-slot>

                            <x-slot:footer>
                                <div class="flex items-center gap-x-2.5">
                                    <x-admin::button
                                        button-type="button"
                                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                                        :title="trans('admin::app.catalog.products.index.create.back-btn')"
                                        @click="attributes = []; $refs.productCreateModal.close()"
                                    />

                                    <x-admin::button
                                        button-type="button"
                                        class="primary-button"
                                        :title="trans('admin::app.catalog.products.index.create.save-btn')"
                                        ::loading="isLoading"
                                        ::disabled="isLoading"
                                    />
                                </div>
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-create-product-form', {
                template: '#v-create-product-form-template',

                data() {
                    return {
                        attributes: [],
                        superAttributes: {},
                        isLoading: false,
                        isCreating: false,
                        pendingType: null,
                    };
                },

                methods: {
                    createAndOpen() {
                        if (this.isCreating) return;

                        @php
                            $defaultType = 'simple';
                            if(!empty($_GET['ingredient'])) $defaultType = 'ingredient';
                        @endphp

                        const type = '{{ $defaultType }}';
                        this.pendingType = type;

                        const params = {
                            type: type,
                            attribute_family_id: '{{ $families[0]->id }}',
                            sku: this.generateUUID(),
                        };

                        this.isCreating = true;
                        this.isLoading = true;

                        this.$axios.post("{{ route('admin.catalog.products.store') }}", params)
                            .then((response) => {
                                this.isLoading = false;

                                if (response.data.data.redirect_url) {
                                    const url = response.data.data.redirect_url;
                                    const match = url.match(/edit\/(\d+)/);
                                    if (match) {
                                        this.$emitter.emit('open-product-drawer', {
                                            product_id: parseInt(match[1]),
                                            name: 'Новый товар',
                                            sku: params.sku,
                                            type: type,
                                        });
                                        this.$emitter.emit('datagrid:refresh');
                                    }
                                } else if (response.data.data.attributes) {
                                    this.attributes = response.data.data.attributes;
                                    this.setSuperAttributes();
                                    this.$refs.productCreateModal.toggle();
                                }
                            })
                            .catch(error => {
                                this.isLoading = false;
                                if (error.response?.status == 422) {
                                    this.$emitter.emit('add-flash', {
                                        type: 'error',
                                        message: Object.values(error.response.data.errors).flat().join(', '),
                                    });
                                }
                            })
                            .finally(() => {
                                this.isCreating = false;
                            });
                    },

                    submitConfigurable(params, { setErrors }) {
                        if (this.isCreating) return;

                        this.isCreating = true;
                        this.isLoading = true;

                        const submitParams = {
                            type: this.pendingType,
                            attribute_family_id: '{{ $families[0]->id }}',
                            sku: this.generateUUID(),
                            super_attributes: this.superAttributes,
                        };

                        this.$axios.post("{{ route('admin.catalog.products.store') }}", submitParams)
                            .then((response) => {
                                this.isLoading = false;

                                if (response.data.data.redirect_url) {
                                    const url = response.data.data.redirect_url;
                                    const match = url.match(/edit\/(\d+)/);
                                    if (match) {
                                        this.$refs.productCreateModal.close();
                                        this.attributes = [];
                                        this.$emitter.emit('open-product-drawer', {
                                            product_id: parseInt(match[1]),
                                            name: 'Новый товар',
                                            sku: submitParams.sku,
                                            type: this.pendingType,
                                        });
                                        this.$emitter.emit('datagrid:refresh');
                                    }
                                }
                            })
                            .catch(error => {
                                this.isLoading = false;
                                if (error.response?.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            })
                            .finally(() => {
                                this.isCreating = false;
                            });
                    },

                    removeOption(option) {
                        this.attributes.forEach(attribute => {
                            attribute.options = attribute.options.filter(item => item.id != option.id);
                        });
                        this.attributes = this.attributes.filter(attribute => attribute.options.length > 0);
                        this.setSuperAttributes();
                    },

                    setSuperAttributes() {
                        this.superAttributes = {};
                        this.attributes.forEach(attribute => {
                            this.superAttributes[attribute.code] = [];
                            attribute.options.forEach(option => {
                                this.superAttributes[attribute.code].push(option.id);
                            });
                        });
                    },

                    generateUUID() {
                        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                            const r = Math.random() * 16 | 0;
                            const v = c === 'x' ? r : (r & 0x3 | 0x8);
                            return v.toString(16);
                        });
                    },
                }
            });
        </script>

        <!-- ============================================== -->
        <!-- Product Slide-Out Drawer Component (SPA)       -->
        <!-- ============================================== -->
        <script type="text/x-template" id="v-product-drawer-template">
            <teleport to="body">
                <div
                    :style="{
                        position: 'fixed',
                        inset: 0,
                        zIndex: 9998,
                        visibility: isOpen ? 'visible' : 'hidden',
                        pointerEvents: isOpen ? 'auto' : 'none',
                    }"
                >
                    <!-- Backdrop -->
                    <div
                        @click="closeDrawer"
                        :style="{
                            position: 'absolute',
                            inset: 0,
                            background: 'rgba(0,0,0,0.3)',
                            backdropFilter: 'blur(2px)',
                            transition: 'opacity 0.3s ease',
                            opacity: panelVisible ? 1 : 0,
                        }"
                    ></div>

                    <!-- Slide-out Panel -->
                    <div
                        ref="drawerPanel"
                        style="position:absolute; top:0; right:0; bottom:0; width:calc(100vw - 270px); max-width:calc(100vw - 270px); background:#f8f9fb; box-shadow:-8px 0 40px rgba(0,0,0,0.15); transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1); overflow:hidden; display:flex; flex-direction:column;"
                        :style="{ transform: panelVisible ? 'translateX(0)' : 'translateX(100%)' }"
                    >
                        <!-- Drawer Header -->
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:11px 24px; background:white; border-bottom:1px solid #e5e7eb; flex-shrink:0; overflow:visible; z-index:10; position:relative;">
                            <div style="display:flex; align-items:center; gap:14px; min-width:0; flex:1; overflow:hidden;">
                                <!-- Close Button -->
                                <button
                                    @click="closeDrawer"
                                    style="display:flex; align-items:center; justify-content:center; width:38px; height:38px; min-width:38px; border-radius:12px; background:#f3f4f6; border:none; cursor:pointer; transition:all 0.2s;"
                                    @mouseenter="$event.currentTarget.style.background='#e5e7eb'; $event.currentTarget.style.transform='scale(1.05)'"
                                    @mouseleave="$event.currentTarget.style.background='#f3f4f6'; $event.currentTarget.style.transform='scale(1)'"
                                    title="Закрыть (Esc)"
                                >
                                    <svg style="width:18px; height:18px; color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <!-- Product Info -->
                                <div style="display:flex; align-items:center; gap:10px; min-width:0; flex:1;">
                                    <div style="display:flex; align-items:center; justify-content:center; width:38px; height:38px; min-width:38px; border-radius:10px; background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow:0 2px 8px rgba(245,158,11,0.3);">
                                        <svg style="width:18px; height:18px; color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <div style="min-width:0;">
                                        <span style="font-size:16px; font-weight:800; color:#1f2937; letter-spacing:-0.02em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; max-width:300px;">
                                            @{{ productRecord.name || 'Товар' }}
                                        </span>
                                        <span style="font-size:12px; color:#9ca3af; display:block;">
                                            ID: @{{ productRecord.product_id }} &bull; @{{ productRecord.sku || '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex; align-items:center; gap:8px; flex-shrink:0; margin-left:12px;">
                                <!-- Delete Button -->
                                <button
                                    @click="deleteProduct"
                                    style="display:flex; align-items:center; gap:6px; padding:8px 14px; border-radius:10px; background:#fef2f2; border:1px solid #fecaca; cursor:pointer; transition:all 0.2s; font-size:13px; font-weight:600; color:#ef4444;"
                                    @mouseenter="$event.currentTarget.style.background='#fee2e2'; $event.currentTarget.style.borderColor='#fca5a5'"
                                    @mouseleave="$event.currentTarget.style.background='#fef2f2'; $event.currentTarget.style.borderColor='#fecaca'"
                                    title="Удалить товар"
                                >
                                    <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Удалить
                                </button>

                                <!-- Open Full Page -->
                                <a
                                    :href="`/admin/catalog/products/edit/${productRecord.product_id}`"
                                    style="display:flex; align-items:center; gap:6px; padding:8px 16px; font-size:13px; font-weight:700; color:white; background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius:12px; text-decoration:none; box-shadow:0 2px 8px rgba(245,158,11,0.3); transition:all 0.2s; white-space:nowrap;"
                                    @mouseenter="$event.currentTarget.style.boxShadow='0 4px 16px rgba(245,158,11,0.4)'"
                                    @mouseleave="$event.currentTarget.style.boxShadow='0 2px 8px rgba(245,158,11,0.3)'"
                                >
                                    <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Открыть
                                </a>
                            </div>
                        </div>

                        <!-- Loading Overlay -->
                        <div v-if="isLoading && isOpen" style="flex:1; display:flex; align-items:center; justify-content:center; background:rgba(248,249,251,0.9); position:absolute; top:60px; left:0; right:0; bottom:0; z-index:2;">
                            <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
                                <div style="width:48px; height:48px; border-radius:16px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%); animation:pulse 1.5s infinite;">
                                    <svg style="width:24px; height:24px; color:white; animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                                        <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <p style="font-size:14px; color:#9ca3af; font-weight:500;">Загрузка товара...</p>
                            </div>
                        </div>

                        <!-- Iframe Content -->
                        <iframe
                            v-if="iframeSrc"
                            :src="iframeSrc"
                            ref="productIframe"
                            @load="onIframeLoad"
                            style="width:100%; border:none; flex:1; margin:0; padding:0; display:block;"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </teleport>
        </script>

        <script type="module">
            app.component('v-product-drawer', {
                template: '#v-product-drawer-template',

                data() {
                    return {
                        isOpen: false,
                        panelVisible: false,
                        isLoading: false,
                        iframeSrc: '',
                        productRecord: {},
                        currentProductId: null,
                    };
                },

                mounted() {
                    this.$emitter.on('open-product-drawer', (record) => {
                        this.openDrawer(record);
                    });

                    window.addEventListener('message', this.handleMessage);
                    window.addEventListener('keydown', this.handleKeyDown);
                },

                beforeUnmount() {
                    window.removeEventListener('message', this.handleMessage);
                    window.removeEventListener('keydown', this.handleKeyDown);
                },

                methods: {
                    openDrawer(record) {
                        this.productRecord = record;
                        this.isLoading = true;
                        this.currentProductId = record.product_id;
                        this.iframeSrc = window.location.origin + '/admin/catalog/products/edit-panel/' + record.product_id;

                        this.isOpen = true;

                        this.$nextTick(() => {
                            requestAnimationFrame(() => {
                                this.panelVisible = true;
                            });
                        });

                        this.toggleSidebarBlur(true);
                        document.body.style.overflow = 'hidden';
                    },

                    closeDrawer() {
                        this.panelVisible = false;

                        setTimeout(() => {
                            this.isOpen = false;
                            this.iframeSrc = '';
                            this.currentProductId = null;
                            this.toggleSidebarBlur(false);
                            document.body.style.overflow = '';
                        }, 350);
                    },

                    onIframeLoad() {
                        this.isLoading = false;
                    },

                    handleMessage(event) {
                        if (!event.data || typeof event.data !== 'object') return;

                        switch (event.data.type) {
                            case 'close-product-panel':
                                this.closeDrawer();
                                break;
                            case 'product-updated':
                                this.$emitter.emit('datagrid:refresh');
                                break;
                            case 'product-deleted':
                                this.closeDrawer();
                                this.$emitter.emit('datagrid:refresh');
                                break;
                        }
                    },

                    handleKeyDown(e) {
                        if (e.key === 'Escape' && this.isOpen) {
                            this.closeDrawer();
                        }
                    },

                    toggleSidebarBlur(blur) {
                        const sidebar = document.querySelector('.lg\\:fixed.lg\\:top-\\[58px\\]');
                        if (sidebar) {
                            sidebar.style.transition = 'filter 0.3s ease';
                            sidebar.style.filter = blur ? 'blur(4px)' : 'none';
                            sidebar.style.pointerEvents = blur ? 'none' : '';
                        }
                    },

                    deleteProduct() {
                        if (!this.productRecord?.product_id) return;

                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.$axios.delete(`/admin/catalog/products/edit/${this.productRecord.product_id}`)
                                    .then(response => {
                                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                        this.closeDrawer();
                                        this.$emitter.emit('datagrid:refresh');
                                    })
                                    .catch(error => {
                                        this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Ошибка удаления' });
                                    });
                            }
                        });
                    },
                },
            });
        </script>

        <style>
            @keyframes spin { to { transform: rotate(360deg); } }
            @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }
        </style>

        <!-- ============================================== -->
        <!-- Inline Product Status Component                -->
        <!-- ============================================== -->
        <script type="text/x-template" id="v-inline-product-status-template">
            <div class="relative inline-flex" ref="statusTrigger" @click.stop>
                <button
                    type="button"
                    @click="toggle"
                    class="inline-flex items-center gap-1"
                    :disabled="isLoading"
                    style="cursor:pointer;"
                >
                    <span
                        style="display:inline-flex; align-items:center; padding:2px 10px; border-radius:9999px; font-size:12px; font-weight:600; width:fit-content; transition:all 0.2s;"
                        :style="currentStatus
                            ? 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;'
                            : 'background:#eef2ff; color:#4f46e5; border:1px solid #c7d2fe;'"
                    >
                        @{{ currentStatus ? 'Активный' : 'Неактивный' }}
                    </span>
                    <svg
                        class="w-3 h-3 text-gray-400 transition-transform duration-200"
                        :style="{ transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)' }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <Teleport to="body">
                    <div
                        v-if="isOpen"
                        class="fixed z-[10020] min-w-[180px] rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-700 dark:bg-gray-900"
                        :style="dropdownStyle"
                    >
                        <button
                            v-for="opt in statusOptions"
                            :key="opt.value"
                            type="button"
                            @click="changeStatus(opt.value)"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <span class="flex items-center gap-2">
                                <span
                                    class="inline-block h-2.5 w-2.5 rounded-full"
                                    :style="{ backgroundColor: opt.color }"
                                ></span>
                                <span class="text-gray-700 dark:text-gray-200">@{{ opt.label }}</span>
                            </span>
                            <svg
                                v-if="opt.value === currentStatus"
                                class="h-4 w-4 text-emerald-600"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </Teleport>
            </div>
        </script>

        <script type="module">
            app.component('v-inline-product-status', {
                template: '#v-inline-product-status-template',

                props: {
                    productId: { type: [String, Number], required: true },
                    initialStatus: { type: [Boolean, Number], default: false },
                },

                data() {
                    return {
                        isOpen: false,
                        isLoading: false,
                        currentStatus: !!this.initialStatus,
                        statusOptions: [
                            { value: true,  label: 'Активный',    color: '#059669' },
                            { value: false, label: 'Неактивный',  color: '#4f46e5' },
                        ],
                        dropdownStyle: {},
                    };
                },

                mounted() {
                    document.addEventListener('click', this.handleOutsideClick);
                },

                beforeUnmount() {
                    document.removeEventListener('click', this.handleOutsideClick);
                },

                methods: {
                    toggle() {
                        if (this.isLoading) return;
                        this.isOpen = !this.isOpen;
                        if (this.isOpen) this.$nextTick(() => this.positionDropdown());
                    },

                    positionDropdown() {
                        const rect = this.$refs.statusTrigger.getBoundingClientRect();
                        this.dropdownStyle = { top: (rect.bottom + 4) + 'px', left: rect.left + 'px' };
                    },

                    handleOutsideClick(e) {
                        if (!this.$el.contains(e.target)) this.isOpen = false;
                    },

                    async changeStatus(val) {
                        if (val === this.currentStatus || this.isLoading) {
                            this.isOpen = false;
                            return;
                        }

                        this.isLoading = true;
                        try {
                            await this.$axios.post('/admin/catalog/products/quick-update/' + this.productId, {
                                field: 'status',
                                value: val ? 1 : 0,
                            });
                            this.currentStatus = val;
                            this.isOpen = false;
                            this.$emitter.emit('add-flash', { type: 'success', message: 'Статус обновлён' });
                        } catch (e) {
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Ошибка обновления статуса' });
                        } finally {
                            this.isLoading = false;
                        }
                    },
                },
            });
        </script>

        <!-- ============================================== -->
        <!-- Inline Product Type Component                  -->
        <!-- ============================================== -->
        <script type="text/x-template" id="v-inline-product-type-template">
            <div class="relative inline-flex" ref="typeTrigger" @click.stop>
                <button
                    type="button"
                    @click="toggle"
                    class="inline-flex items-center gap-1"
                    :disabled="isLoading"
                    style="cursor:pointer;"
                >
                    <span style="display:inline-flex; align-items:center; padding:1px 8px; border-radius:6px; font-size:11px; background:#f3f4f6; color:#6b7280; font-weight:500; width:fit-content;">
                        @{{ typeLabels[currentType] || currentType }}
                    </span>
                    <svg
                        class="w-3 h-3 text-gray-400 transition-transform duration-200"
                        :style="{ transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)' }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <Teleport to="body">
                    <div
                        v-if="isOpen"
                        class="fixed z-[10020] min-w-[220px] rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-700 dark:bg-gray-900"
                        :style="dropdownStyle"
                    >
                        <button
                            v-for="(label, key) in typeLabels"
                            :key="key"
                            type="button"
                            @click="changeType(key)"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <span class="text-gray-700 dark:text-gray-200">@{{ label }}</span>
                            <svg
                                v-if="key === currentType"
                                class="h-4 w-4 text-emerald-600"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </Teleport>
            </div>
        </script>

        <script type="module">
            app.component('v-inline-product-type', {
                template: '#v-inline-product-type-template',

                props: {
                    productId: { type: [String, Number], required: true },
                    initialType: { type: String, default: 'simple' },
                },

                data() {
                    return {
                        isOpen: false,
                        isLoading: false,
                        currentType: this.initialType,
                        typeLabels: {
                            simple: 'Простой',
                            configurable: 'Настроенный',
                            grouped: 'Группированный',
                            bundle: 'Набор',
                            ingredient: 'Ингредиент',
                            constructor: 'Конструктор',
                            configurable_constructor: 'Настроенный + Конструктор',
                        },
                        dropdownStyle: {},
                    };
                },

                mounted() {
                    document.addEventListener('click', this.handleOutsideClick);
                },

                beforeUnmount() {
                    document.removeEventListener('click', this.handleOutsideClick);
                },

                methods: {
                    toggle() {
                        if (this.isLoading) return;
                        this.isOpen = !this.isOpen;
                        if (this.isOpen) this.$nextTick(() => this.positionDropdown());
                    },

                    positionDropdown() {
                        const rect = this.$refs.typeTrigger.getBoundingClientRect();
                        this.dropdownStyle = { top: (rect.bottom + 4) + 'px', left: rect.left + 'px' };
                    },

                    handleOutsideClick(e) {
                        if (!this.$el.contains(e.target)) this.isOpen = false;
                    },

                    async changeType(val) {
                        if (val === this.currentType || this.isLoading) {
                            this.isOpen = false;
                            return;
                        }

                        this.isLoading = true;
                        try {
                            await this.$axios.post('/admin/catalog/products/quick-update/' + this.productId, {
                                field: 'type',
                                value: val,
                            });
                            this.currentType = val;
                            this.isOpen = false;
                            this.$emitter.emit('add-flash', { type: 'success', message: 'Тип товара изменён' });
                        } catch (e) {
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Ошибка смены типа' });
                        } finally {
                            this.isLoading = false;
                        }
                    },
                },
            });
        </script>

        <!-- ============================================== -->
        <!-- Inline Product Category Component              -->
        <!-- ============================================== -->
        <script type="text/x-template" id="v-inline-product-category-template">
            <div class="relative inline-flex" ref="catTrigger" @click.stop>
                <button
                    type="button"
                    @click="toggle"
                    class="inline-flex items-center gap-1"
                    :disabled="isLoading"
                    style="cursor:pointer;"
                >
                    <span v-if="currentCategory" style="display:inline-flex; align-items:center; padding:1px 8px; border-radius:6px; font-size:11px; background:#fef3c7; color:#d97706; font-weight:500; width:fit-content;">
                        @{{ currentCategory }}
                    </span>
                    <span v-else class="text-gray-400" style="font-size:12px;">&mdash;</span>
                    <svg
                        class="w-3 h-3 text-gray-400 transition-transform duration-200"
                        :style="{ transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)' }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <Teleport to="body">
                    <div
                        v-if="isOpen"
                        class="fixed z-[10020] min-w-[240px] max-h-[320px] overflow-y-auto rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-700 dark:bg-gray-900"
                        :style="dropdownStyle"
                    >
                        <!-- Search -->
                        <div class="sticky top-0 bg-white dark:bg-gray-900 p-1">
                            <input
                                type="text"
                                v-model="searchQuery"
                                @input="searchCategories"
                                placeholder="Поиск категории..."
                                class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <div v-if="isSearching" class="flex justify-center py-3">
                            <svg class="w-5 h-5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>

                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            type="button"
                            @click="selectCategory(cat)"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <span class="text-gray-700 dark:text-gray-200">@{{ cat.name }}</span>
                            <svg
                                v-if="selectedCategoryId === cat.id"
                                class="h-4 w-4 text-emerald-600"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>

                        <p v-if="!isSearching && categories.length === 0" class="text-center text-xs text-gray-400 py-3">
                            Категории не найдены
                        </p>
                    </div>
                </Teleport>
            </div>
        </script>

        <script type="module">
            app.component('v-inline-product-category', {
                template: '#v-inline-product-category-template',

                props: {
                    productId: { type: [String, Number], required: true },
                    initialCategory: { type: String, default: '' },
                },

                data() {
                    return {
                        isOpen: false,
                        isLoading: false,
                        isSearching: false,
                        currentCategory: this.initialCategory || '',
                        selectedCategoryId: null,
                        searchQuery: '',
                        categories: [],
                        searchTimer: null,
                        dropdownStyle: {},
                    };
                },

                mounted() {
                    document.addEventListener('click', this.handleOutsideClick);
                },

                beforeUnmount() {
                    document.removeEventListener('click', this.handleOutsideClick);
                },

                methods: {
                    toggle() {
                        if (this.isLoading) return;
                        this.isOpen = !this.isOpen;
                        if (this.isOpen) {
                            this.$nextTick(() => this.positionDropdown());
                            if (this.categories.length === 0) this.loadCategories();
                        }
                    },

                    positionDropdown() {
                        const rect = this.$refs.catTrigger.getBoundingClientRect();
                        const spaceBelow = window.innerHeight - rect.bottom;
                        if (spaceBelow < 330) {
                            this.dropdownStyle = { bottom: (window.innerHeight - rect.top + 4) + 'px', left: rect.left + 'px' };
                        } else {
                            this.dropdownStyle = { top: (rect.bottom + 4) + 'px', left: rect.left + 'px' };
                        }
                    },

                    handleOutsideClick(e) {
                        if (!this.$el.contains(e.target)) this.isOpen = false;
                    },

                    async loadCategories() {
                        this.isSearching = true;
                        try {
                            const resp = await this.$axios.get('/admin/catalog/categories/search', {
                                params: { query: this.searchQuery || '', limit: 50 }
                            });
                            // Response is paginated: { data: [...], current_page, ... }
                            const raw = resp.data;
                            this.categories = Array.isArray(raw) ? raw : (raw?.data || []);
                        } catch (e) {
                            this.categories = [];
                        } finally {
                            this.isSearching = false;
                        }
                    },

                    searchCategories() {
                        clearTimeout(this.searchTimer);
                        this.searchTimer = setTimeout(() => this.loadCategories(), 300);
                    },

                    async selectCategory(cat) {
                        if (this.isLoading) return;
                        this.isLoading = true;
                        try {
                            await this.$axios.post('/admin/catalog/products/quick-update/' + this.productId, {
                                field: 'category',
                                value: [cat.id],
                            });
                            this.currentCategory = cat.name;
                            this.selectedCategoryId = cat.id;
                            this.isOpen = false;
                            this.$emitter.emit('add-flash', { type: 'success', message: 'Категория обновлена' });
                        } catch (e) {
                            this.$emitter.emit('add-flash', { type: 'error', message: 'Ошибка обновления категории' });
                        } finally {
                            this.isLoading = false;
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
