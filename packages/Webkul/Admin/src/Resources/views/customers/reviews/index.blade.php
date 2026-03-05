<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.customers.reviews.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 15px rgba(245,158,11,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.customers.reviews.index.title')
                </p>
                <p class="text-xs text-gray-400">Отзывы клиентов</p>
            </div>
        </div>
    </div>

    {!! view_render_event('bagisto.admin.customers.reviews.edit.before') !!}

    <v-review-edit-drawer></v-review-edit-drawer>

    {!! view_render_event('bagisto.admin.customers.groups.edit.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-review-edit-drawer-template"
        >

            {!! view_render_event('bagisto.admin.customers.reviews.list.before') !!}

            <x-admin::datagrid
                :src="route('admin.customers.customers.review.index')"
                :isMultiRow="true"
                ref="review_data"
            >
                @php
                    $hasPermission = bouncer()->hasPermission('customers.reviews.edit') || bouncer()->hasPermission('customers.reviews.delete');
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
                        <div class="row grid grid-cols-1 md:grid-cols-[2fr_1fr_minmax(150px,_4fr)_0.5fr] grid-rows-1 gap-1 items-center border-b px-4 py-2.5 dark:border-gray-800 min-w-full">
                            <div
                                class="flex items-center gap-2.5"
                                v-for="(columnGroup, index) in [['customer_full_name', 'product_name', 'product_review_status'], ['rating', 'created_at', 'product_review_id'], ['title', 'comment']]"
                            >
                                @if ($hasPermission)
                                    <label
                                        class="flex w-max cursor-pointer select-none items-center gap-1"
                                        for="mass_action_select_all_records"
                                        v-if="! index"
                                    >
                                        <input
                                            type="checkbox"
                                            id="mass_action_select_all_records"
                                            class="peer hidden"
                                            name="mass_action_select_all_records"
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

                                <!-- Product Name, Review Status -->
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
                            class="row grid grid-cols-1 gap-2 md:grid-cols-[2fr_1fr_minmax(150px,_4fr)_0.5fr] md:gap-0 border-b px-4 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950 min-w-full"
                            style="border-left: 3px solid transparent; transition: all 0.15s;"
                            @mouseenter="$event.currentTarget.style.borderLeftColor='#f59e0b'"
                            @mouseleave="$event.currentTarget.style.borderLeftColor='transparent'"
                            v-for="record in available.records"
                        >
                            <!-- Name, Product, Status -->
                            <div class="flex gap-2.5">
                                @if ($hasPermission)
                                    <input
                                        type="checkbox"
                                        :id="`mass_action_select_record_${record.product_review_id}`"
                                        class="peer hidden"
                                        :name="`mass_action_select_record_${record.product_review_id}`"
                                        :value="record.product_review_id"
                                        v-model="applied.massActions.indices"
                                        @change="setCurrentSelectionMode"
                                    >

                                    <label
                                        class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                                        :for="`mass_action_select_record_${record.product_review_id}`"
                                    ></label>
                                @endif

                                <div class="flex items-start gap-3">
                                    <!-- Avatar circle -->
                                    <div
                                        class="flex items-center justify-center w-9 h-9 rounded-full flex-shrink-0"
                                        style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); font-size: 13px; font-weight: 700; color: white; min-width: 36px;"
                                    >
                                        @{{ record.customer_full_name ? record.customer_full_name.charAt(0).toUpperCase() : '?' }}
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                                            @{{ record.customer_full_name }}
                                        </p>

                                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                                            @{{ record.product_name }}
                                        </p>

                                        <p v-html="record.product_review_status"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Rating, Date, Id Section -->
                            <div class="flex flex-col gap-1.5 ps-8 md:ps-0">
                                <div class="flex">
                                    <x-admin::star-rating
                                        :is-editable="false"
                                        ::value="record.rating"
                                    />
                                </div>

                                <p class="text-gray-500 dark:text-gray-400 text-xs">
                                    @{{ record.created_at }}
                                </p>

                                <span
                                    style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; background: #f3f4f6; color: #6b7280; width: fit-content;"
                                >
                                    #@{{ record.product_review_id }}
                                </span>
                            </div>

                            <!-- Title, Description -->
                            <div class="flex flex-col gap-1.5 ps-8 md:ps-0">
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @{{ record.title }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300 text-sm" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    @{{ record.comment }}
                                </p>
                            </div>

                            <div class="flex place-content-end items-center gap-1 self-center">
                                <!-- Review Delete Button -->
                                <a @click="performAction(record.actions.find(action => action.index === 'delete'))" style="cursor: pointer;">
                                    <span
                                        :class="record.actions.find(action => action.index === 'delete')?.icon"
                                        class="cursor-pointer rounded-lg p-1.5 text-2xl transition-all ltr:ml-1 rtl:mr-1"
                                        style="color: #ef4444;"
                                        @mouseenter="$event.target.style.background='#fef2f2'"
                                        @mouseleave="$event.target.style.background='transparent'"
                                    >
                                    </span>
                                </a>

                                <!-- View Button -->
                                <a
                                    v-if="record.actions.find(action => action.index === 'edit')"
                                    @click="edit(record.actions.find(action => action.index === 'edit')?.url)"
                                    style="cursor: pointer;"
                                >
                                    <span
                                        class="icon-sort-right rtl:icon-sort-left cursor-pointer rounded-lg p-1.5 text-2xl transition-all ltr:ml-1 rtl:mr-1"
                                        style="color: #6366f1;"
                                        @mouseenter="$event.target.style.background='#eef2ff'"
                                        @mouseleave="$event.target.style.background='transparent'"
                                    ></span>
                                </a>
                            </div>
                        </div>
                    </template>
                </template>
            </x-admin::datagrid>

            {!! view_render_event('bagisto.admin.customers.reviews.list.after') !!}

            <!-- Drawer content -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form
                        @submit="handleSubmit($event, update)"
                        ref="reviewCreateForm"
                    >
                        <x-admin::drawer ref="review">
                            <!-- Drawer Header -->
                            <x-slot:header>
                                <div class="flex items-center justify-between">
                                    <p class="text-xl font-medium dark:text-white">
                                        @lang('admin::app.customers.reviews.index.edit.title')
                                    </p>

                                    <button class="primary-button ltr:mr-11 rtl:ml-11">
                                        @lang('admin::app.customers.reviews.index.edit.save-btn')
                                    </button>
                                </div>
                            </x-slot>

                            <!-- Drawer Content -->
                            <x-slot:content>
                                <div class="flex flex-col gap-4 px-1.5 py-2.5">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="">
                                            <!-- Customer Name -->
                                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.customers.reviews.index.edit.customer')
                                            </p>

                                            <p class="font-semibold text-gray-800 dark:text-white">
                                                @{{ review.name !== '' ? review.name : 'N/A' }}
                                            </p>
                                        </div>

                                        <div class="">
                                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.customers.reviews.index.edit.product')
                                            </p>

                                            <p class="font-semibold text-gray-800 dark:text-white">
                                                @{{ review.product.name }}
                                            </p>
                                        </div>

                                        <div class="">
                                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.customers.reviews.index.edit.id')
                                            </p>

                                            <p class="font-semibold text-gray-800 dark:text-white">
                                                @{{ review.id }}
                                            </p>
                                        </div>

                                        <div class="">
                                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.customers.reviews.index.edit.date')
                                            </p>

                                            <p class="font-semibold text-gray-800 dark:text-white">
                                                @{{ review.date }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="w-full">
                                        <x-admin::form.control-group.control
                                            type="hidden"
                                            name="id"
                                            rules="required"
                                            ::value="review.id"
                                        />

                                        <x-admin::form.control-group>
                                            <x-admin::form.control-group.label class="required">
                                                @lang('admin::app.customers.reviews.index.edit.status')
                                            </x-admin::form.control-group.label>

                                            <x-admin::form.control-group.control
                                                type="select"
                                                name="status"
                                                rules="required"
                                                ::value="review.status"
                                            >
                                                <option value="approved" >
                                                    @lang('admin::app.customers.reviews.index.edit.approved')
                                                </option>

                                                <option value="disapproved">
                                                    @lang('admin::app.customers.reviews.index.edit.disapproved')
                                                </option>

                                                <option value="pending">
                                                    @lang('admin::app.customers.reviews.index.edit.pending')
                                                </option>
                                            </x-admin::form.control-group.control>

                                            <x-admin::form.control-group.error control-name="status" />
                                        </x-admin::form.control-group>
                                    </div>

                                    <div class="w-full">
                                        <p class="font-semibold text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.customers.reviews.index.edit.rating')
                                        </p>

                                        <div class="flex">
                                            <x-admin::star-rating
                                                :is-editable="false"
                                                ::value="review.rating"
                                            />
                                        </div>
                                    </div>

                                    <div class="w-full">
                                        <p class="block text-xs font-medium leading-6 text-gray-800 dark:text-white">
                                            @lang('admin::app.customers.reviews.index.edit.review-title')
                                        </p>

                                        <p class="font-semibold text-gray-800 dark:text-white">
                                            @{{ review.title }}
                                        </p>
                                    </div>

                                    <div class="w-full">
                                        <p class="block text-xs font-semibold leading-6 text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.customers.reviews.index.edit.review-comment')
                                        </p>

                                        <p class="text-gray-800 dark:text-white">
                                            @{{ review.comment }}
                                        </p>
                                    </div>

                                    <div
                                        class="w-full"
                                        v-if="review.images.length"
                                    >
                                        <x-admin::form.control-group.label>
                                            @lang('admin::app.customers.reviews.index.edit.images')
                                        </x-admin::form.control-group.label>

                                        <div class="flex flex-wrap gap-4">
                                            <div v-for="image in review.images" :key="image.id">
                                                <img
                                                    :src="image.url"
                                                    class="h-[60px] w-[60px] rounded"
                                                    v-if="image.type === 'image'"
                                                    alt="Image"
                                                />

                                                <video
                                                    v-else
                                                    class="h-[60px] w-[60px] rounded"
                                                    controls
                                                    autoplay
                                                >
                                                    <source
                                                        :src="image.url"
                                                        type="video/mp4"
                                                    >
                                                </video>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </x-slot>
                        </x-admin::drawer>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-review-edit-drawer', {
                template: '#v-review-edit-drawer-template',

                data() {
                    return {
                        review: {},
                    }
                },

                methods: {
                    edit(url) {
                        this.$axios.get(url)
                            .then((response) => {
                                this.$refs.review.open(),

                                this.review = response.data.data
                            })
                            .catch(error => {
                                if (error.response.status ==422) {
                                    setErrors(error.response.data.errors);
                                }
                            });

                    },

                    update(params) {
                        let formData = new FormData(this.$refs.reviewCreateForm);

                        formData.append('_method', 'put');

                        this.$axios.post(`{{ route('admin.customers.customers.review.update', '') }}/${params.id}`, formData)
                            .then((response) => {
                                this.$refs.review.close();

                                this.$refs.review_data.get();

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            })
                            .catch(error => {
                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            });
                    },
                }
            })
        </script>
    @endPushOnce
</x-admin::layouts>
