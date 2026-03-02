<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.products.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.catalog.products.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <!-- Export Modal -->
            <x-admin::datagrid.export :src="route('admin.catalog.products.index')" />

            {!! view_render_event('bagisto.admin.catalog.products.create.before') !!}

            @if (bouncer()->hasPermission('catalog.products.create'))
                <v-quick-create-product>
                    <button
                        type="button"
                        class="primary-button"
                    >
                        @lang('admin::app.catalog.products.index.create-btn')
                    </button>
                </v-quick-create-product>
            @endif

            {!! view_render_event('bagisto.admin.catalog.products.create.after') !!}
        </div>
    </div>

    {!! view_render_event('bagisto.admin.catalog.products.list.before') !!}

    <!-- Mass Action Floating Panel -->
    <v-product-mass-action></v-product-mass-action>

    <!-- Datagrid -->
    <x-admin::datagrid
        :src="route('admin.catalog.products.index')"
        :isMultiRow="true"
        ref="productDatagrid"
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
                <!-- Modern Grid Header -->
                <div class="hidden md:grid grid-cols-[2fr_1fr_1fr] items-center bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800 px-5 py-3.5 rounded-t-xl">
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
                                        applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checked peer-checked:text-violet-600' : (
                                            applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-partial peer-checked:text-violet-600' : ''
                                        ),
                                    ]"
                                >
                                </span>
                            </label>
                        @endif

                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <span>
                                <template v-for="(column, idx) in columnGroup">
                                    <span
                                        class="transition-colors duration-200"
                                        :class="{
                                            'text-violet-600 dark:text-violet-400': applied.sort.column == column,
                                            'cursor-pointer hover:text-violet-600 dark:hover:text-violet-400': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                        }"
                                        @click="
                                            available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)): {}
                                        "
                                    >
                                        @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                    </span>
                                    <span v-if="idx < columnGroup.length - 1" class="mx-1 text-gray-300 dark:text-gray-600">/</span>
                                </template>
                            </span>

                            <i
                                class="align-text-bottom text-sm text-violet-600 dark:text-violet-400 ltr:ml-1 rtl:mr-1 transition-transform duration-200"
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
                <!-- Modern Product Cards -->
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        class="group grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr] gap-4 md:gap-6 px-5 py-4 transition-all duration-300 hover:bg-gradient-to-r hover:from-violet-50/50 hover:to-transparent dark:hover:from-violet-900/10 relative"
                        v-for="(record, index) in available.records"
                        :key="record.product_id"
                    >
                        <!-- Hover indicator -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-violet-500 rounded-r opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <!-- Name, SKU, Attribute Family -->
                        <div class="flex items-start gap-4">
                            @if ($hasPermission)
                                <div class="flex-shrink-0 pt-1">
                                    <input
                                        type="checkbox"
                                        :name="`mass_action_select_record_${record.product_id}`"
                                        :id="`mass_action_select_record_${record.product_id}`"
                                        :value="record.product_id"
                                        class="peer hidden"
                                        v-model="applied.massActions.indices"
                                    >

                                    <label
                                        class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-violet-600 transition-colors"
                                        :for="`mass_action_select_record_${record.product_id}`"
                                    ></label>
                                </div>
                            @endif

                            <div class="flex flex-col gap-1.5 min-w-0 flex-1">
                                <a class="text-base font-bold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors duration-200 cursor-pointer truncate"
                                   :href="`/admin/catalog/products/edit/${record.product_id}`"
                                >
                                    @{{ record.name }}
                                </a>

                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded-md font-mono">
                                        @{{ record.sku }}
                                    </span>
                                </div>

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ record.attribute_family || 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Image, Price, Stock, ID -->
                        <div class="flex items-center gap-4">
                            <!-- Product Image -->
                            <div class="relative flex-shrink-0">
                                <template v-if="record.base_image">
                                    <div class="h-14 w-14 rounded-xl overflow-hidden ring-2 ring-white dark:ring-gray-900 shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-200">
                                        <img
                                            class="h-full w-full object-cover"
                                            :src='record.base_image'
                                        />
                                    </div>

                                    <span v-if="record.images_count > 1" class="absolute -bottom-1 -right-1 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-violet-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-900 px-1">
                                        @{{ record.images_count }}
                                    </span>
                                </template>

                                <template v-else>
                                    <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center ring-2 ring-white dark:ring-gray-900 shadow-md">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            <div class="flex flex-col gap-1">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    @{{ $admin.formatPrice(record.price, (record.type === 'constructor' || record.type === 'grouped'), record.selected_ingredients_sum*1, record) }}
                                </p>

                                <!-- Stock Status -->
                                <div v-if="['configurable', 'bundle', 'grouped', 'booking'].includes(record.type)">
                                    <span class="text-xs text-gray-400">N/A</span>
                                </div>
                                <div v-else>
                                    <span v-if="record.quantity > 0" class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        @{{ record.quantity }} в наличии
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 text-xs font-medium text-rose-600 dark:text-rose-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        @lang('admin::app.catalog.products.index.datagrid.out-of-stock')
                                    </span>
                                </div>

                                <p class="text-xs text-gray-400">
                                    ID - @{{ record.product_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Status, Category, Type, Actions -->
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex flex-col gap-1.5">
                                <p :class="[record.status ? 'label-active': 'label-info']">
                                    @{{ record.status ? "@lang('admin::app.catalog.products.index.datagrid.active')" : "@lang('admin::app.catalog.products.index.datagrid.disable')" }}
                                </p>

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ record.category_name ?? 'N/A' }}
                                </p>

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ record.type }}
                                </p>
                            </div>

                            <div
                                class="flex items-center gap-1"
                                v-if="available.actions.length"
                            >
                                <span
                                    class="cursor-pointer w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-violet-100 dark:hover:bg-violet-900/30 hover:text-violet-600 dark:hover:text-violet-400 transition-colors duration-200"
                                    :class="action.icon"
                                    v-text="! action.icon ? action.title : ''"
                                    v-for="action in record.actions"
                                    @click="performAction(action)"
                                >
                                </span>
                            </div>
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
                        @click="$refs.productCreateModal.toggle()"
                    >
                        @lang('admin::app.catalog.products.index.create-btn')
                    </button>
                @endif

                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, create)">
                        <!-- Customer Create Modal -->
                        <x-admin::modal ref="productCreateModal">
                            <!-- Modal Header -->
                            <x-slot:header>
                                <p
                                    class="text-lg font-bold text-gray-800 dark:text-white"
                                    v-if="! attributes.length"
                                >
                                    @lang('admin::app.catalog.products.index.create.title')
                                </p>

                                <p
                                    class="text-lg font-bold text-gray-800 dark:text-white"
                                    v-else
                                >
                                    @lang('admin::app.catalog.products.index.create.configurable-attributes')
                                </p>
                            </x-slot>

                            <!-- Modal Content -->
                            <x-slot:content>
                                <div v-show="! attributes.length">
                                    {!! view_render_event('bagisto.admin.catalog.products.create_form.general.controls.before') !!}

                                    <!-- Product Type -->
                                    <x-admin::form.control-group >
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.catalog.products.index.create.type')
                                        </x-admin::form.control-group.label>

{{--                                                    TODO refactor--}}
                                        @php
                                        $value = 'simple';
                                            if(!empty($_GET['ingredient']))
                                                $value = 'ingredient';
                                            elseif(empty($_GET['ingredient']))
                                                $value = 'simple';
                                        @endphp
                                        <x-admin::form.control-group.control
                                            type="select"
                                            name="type"
                                            rules="required"
                                            :label="trans('admin::app.catalog.products.index.create.type')"
                                            value="{{$value}}"
                                        >
                                            @foreach(config('product_types') as $key => $type)
                                                <option
{{--                                                    TODO refactor--}}
                                                    @if($key == 'ingredient' && !empty($_GET['ingredient'])) selected
                                                    @elseif(empty($_GET['ingredient']) && $key == 'simple') selected
                                                      @endif
                                                    value="{{ $key }}"
                                                >
                                                    @lang($type['name'])
                                                </option>
                                            @endforeach
                                        </x-admin::form.control-group.control>

                                        <x-admin::form.control-group.error control-name="type" />
                                    </x-admin::form.control-group>

                                    <!-- Attribute Family Id -->
                                    <x-admin::form.control-group style="display: none">
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.catalog.products.index.create.family')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="select"
                                            name="attribute_family_id"
                                            rules="required"
                                            :label="trans('admin::app.catalog.products.index.create.family')"
                                            value="{{$families[0]->id}}"
                                        >
                                            @foreach($families as $family)
                                                <option @if($loop->index === 0) selected @endif  value="{{ $family->id }}">
                                                    {{ $family->name }}
                                                </option>
                                            @endforeach
                                        </x-admin::form.control-group.control>

                                        <x-admin::form.control-group.error control-name="attribute_family_id" />
                                    </x-admin::form.control-group>

                                    <!-- SKU -->
                                    <x-admin::form.control-group style="display: none">
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.catalog.products.index.create.sku')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="sku"
                                            ::rules="{ required: false, regex: /^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/ }"
                                            :label="trans('admin::app.catalog.products.index.create.sku')"
                                            value="{{Str::uuid()->toString()}}"
                                        />

                                        <x-admin::form.control-group.error control-name="sku" />
                                    </x-admin::form.control-group>

                                    {!! view_render_event('bagisto.admin.catalog.products.create_form.general.controls.after') !!}
                                </div>

                                <div v-show="attributes.length">
                                    {!! view_render_event('bagisto.admin.catalog.products.create_form.attributes.controls.before') !!}

                                    <div
                                        class="mb-2.5"
                                        v-for="attribute in attributes"
                                    >
                                        <label
                                            class="block text-xs font-medium leading-6 text-gray-800 dark:text-white"
                                            v-text="attribute.name"
                                        >
                                        </label>

                                        <div class="flex min-h-[38px] flex-wrap gap-1 rounded-md border p-1.5 dark:border-gray-800">
                                            <p
                                                class="flex items-center rounded bg-gray-600 px-2 py-1 font-semibold text-white"
                                                v-for="option in attribute.options"
                                            >
                                                @{{ option.name }}

                                                <span
                                                    class="icon-cross cursor-pointer text-lg text-white ltr:ml-1.5 rtl:mr-1.5"
                                                    @click="removeOption(option)"
                                                >
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    {!! view_render_event('bagisto.admin.catalog.products.create_form.attributes.controls.after') !!}
                                </div>
                            </x-slot>

                            <!-- Modal Footer -->
                            <x-slot:footer>
                                <div class="flex items-center gap-x-2.5">
                                    <!-- Back Button -->
                                    <x-admin::button
                                        button-type="button"
                                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                                        :title="trans('admin::app.catalog.products.index.create.back-btn')"
                                        v-if="attributes.length"
                                        @click="attributes = []"
                                    />

                                    <!-- Save Button -->
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
                    };
                },

                methods: {
                    create(params, { resetForm, resetField, setErrors }) {
                        this.isLoading = true;

                        this.attributes.forEach(attribute => {
                            params.super_attributes ||= {};

                            params.super_attributes[attribute.code] = this.superAttributes[attribute.code];
                        });

                        this.$axios.post("{{ route('admin.catalog.products.store') }}", params)
                            .then((response) => {
                                this.isLoading = false;

                                if (response.data.data.redirect_url) {
                                    window.location.href = response.data.data.redirect_url;
                                } else {
                                    this.attributes = response.data.data.attributes;

                                    this.setSuperAttributes();
                                }
                            })
                            .catch(error => {
                                this.isLoading = false;

                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
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
                    }
                }
            })
        </script>

        <!-- Product Mass Action Component -->
        <script type="text/x-template" id="v-product-mass-action-template">
            <transition name="slide-up">
                <div v-if="selectedProducts.length > 0"
                     class="fixed bottom-4 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-3 px-5 py-3 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700"
                     style="box-shadow: 0 -4px 30px rgba(0,0,0,0.15);">

                    <!-- Selected count -->
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30">
                            <span class="text-base font-bold text-violet-600 dark:text-violet-400">@{{ selectedProducts.length }}</span>
                        </div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">
                            выбрано
                        </span>
                    </div>

                    <div class="w-px h-6 bg-gray-200 dark:bg-gray-700"></div>

                    <!-- Status select -->
                    <div class="flex items-center gap-2">
                        <select
                            v-model="selectedStatus"
                            class="px-3 py-1.5 text-sm font-medium border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                        >
                            <option value="">Статус</option>
                            <option value="1">Активен</option>
                            <option value="0">Неактивен</option>
                        </select>
                    </div>

                    <!-- Apply button -->
                    <button
                        @click="applyMassAction"
                        :disabled="selectedStatus === '' || isLoading"
                        class="flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-violet-600 rounded-lg hover:from-violet-600 hover:to-violet-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                    >
                        <svg v-if="isLoading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>OK</span>
                    </button>

                    <!-- Delete button -->
                    <button
                        @click="deleteSelected"
                        :disabled="isLoading"
                        class="flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-white bg-gradient-to-r from-rose-500 to-rose-600 rounded-lg hover:from-rose-600 hover:to-rose-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Удалить</span>
                    </button>

                    <!-- Clear selection -->
                    <button
                        @click="clearSelection"
                        class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200"
                        title="Сбросить выбор"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </transition>
        </script>

        <script type="module">
            app.component('v-product-mass-action', {
                template: '#v-product-mass-action-template',

                data() {
                    return {
                        selectedProducts: [],
                        selectedStatus: '',
                        isLoading: false
                    }
                },

                mounted() {
                    // Watch for changes in datagrid mass action indices
                    this.checkInterval = setInterval(() => {
                        this.checkSelectedProducts();
                    }, 200);
                },

                beforeUnmount() {
                    if (this.checkInterval) {
                        clearInterval(this.checkInterval);
                    }
                },

                methods: {
                    checkSelectedProducts() {
                        // Get selected indices from datagrid
                        const checkboxes = document.querySelectorAll('input[name^="mass_action_select_record_"]:checked');
                        const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));

                        if (JSON.stringify(ids) !== JSON.stringify(this.selectedProducts)) {
                            this.selectedProducts = ids;
                        }
                    },

                    clearSelection() {
                        this.selectedProducts = [];
                        this.selectedStatus = '';

                        // Uncheck all checkboxes
                        document.querySelectorAll('input[name^="mass_action_select_record_"]').forEach(cb => {
                            cb.checked = false;
                        });

                        const selectAllCb = document.getElementById('mass_action_select_all_records');
                        if (selectAllCb) selectAllCb.checked = false;
                    },

                    async applyMassAction() {
                        if (this.selectedStatus === '' || this.selectedProducts.length === 0) return;

                        this.isLoading = true;

                        try {
                            const response = await this.$axios.post('{{ route("admin.catalog.products.mass_update") }}', {
                                indices: this.selectedProducts,
                                value: parseInt(this.selectedStatus)
                            });

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data.message
                            });

                            // Reload the page to refresh data
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } catch (error) {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Произошла ошибка'
                            });
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    async deleteSelected() {
                        if (this.selectedProducts.length === 0) return;

                        if (!confirm(`Вы уверены, что хотите удалить ${this.selectedProducts.length} товар(ов)?`)) {
                            return;
                        }

                        this.isLoading = true;

                        try {
                            const response = await this.$axios.post('{{ route("admin.catalog.products.mass_delete") }}', {
                                indices: this.selectedProducts
                            });

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data.message
                            });

                            // Reload the page to refresh data
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } catch (error) {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Произошла ошибка при удалении'
                            });
                        } finally {
                            this.isLoading = false;
                        }
                    }
                }
            });

            // Компонент быстрого создания товара
            app.component('v-quick-create-product', {
                template: `
                    <div @click="createProduct">
                        <slot></slot>
                    </div>
                `,

                data() {
                    return {
                        isCreating: false
                    };
                },

                methods: {
                    async createProduct() {
                        if (this.isCreating) return;

                        this.isCreating = true;

                        try {
                            const response = await this.$axios.post("{{ route('admin.catalog.products.store') }}", {
                                type: 'simple',
                                attribute_family_id: {{ $families[0]->id }},
                                sku: 'product-' + Date.now()
                            });

                            if (response.data.data.redirect_url) {
                                window.location.href = response.data.data.redirect_url;
                            }
                        } catch (error) {
                            console.error('Error creating product:', error);
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Ошибка при создании товара'
                            });
                            this.isCreating = false;
                        }
                    }
                }
            });
        </script>

        <style>
            .slide-up-enter-active,
            .slide-up-leave-active {
                transition: all 0.3s ease;
            }
            .slide-up-enter-from,
            .slide-up-leave-to {
                opacity: 0;
                transform: translate(-50%, 20px);
            }
        </style>
    @endPushOnce
</x-admin::layouts>
