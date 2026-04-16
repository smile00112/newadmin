<div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
    <div class="flex justify-between">
        <!-- Total Order Count -->
        <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
            @lang('admin::app.customers.customers.view.orders.count', ['count' => count($customer->orders)])
        </p>

        <!-- Total Order Revenue -->
        <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
            @lang('admin::app.customers.customers.view.orders.total-revenue', ['revenue' => core()->formatPrice($customer->orders->whereNotIn('status', ['canceled', 'closed'])->sum('base_grand_total_invoiced'))])
        </p>
    </div>

    <!-- Status Filter Tabs -->
    <v-order-status-filter></v-order-status-filter>

    <x-admin::datagrid
        :src="route('admin.customers.customers.view', [
            'id'   => $customer->id,
            'type' => 'orders'
        ])"
    >
        <!-- Datagrid Header -->
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
                <div class="row grid grid-cols-[0.5fr_0.5fr_1fr] grid-rows-1 items-center border-b border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <div
                        class="flex select-none items-center gap-2.5"
                        v-for="(columnGroup, index) in [['increment_id', 'created_at', 'status'], ['base_grand_total', 'method', 'channel_name'], ['full_name', 'customer_email', 'location', 'image']]"
                    >
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
                    v-if="available.meta.total"
                    class="row grid grid-cols-4 border-b px-4 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- Order Id, Created, Status Section -->
                    <div class="">
                        <div class="flex gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <p
                                    class="text-base font-semibold text-gray-800 dark:text-white"
                                >
                                    @{{ "@lang('admin::app.sales.orders.index.datagrid.id')".replace(':id', record.increment_id) }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ record.created_at }}
                                </p>

                                <p v-html="record.status"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Amount, Pay Via, Channel -->
                    <div class="">
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @{{ $admin.formatPrice(record.base_grand_total) }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.index.datagrid.pay-by', ['method' => ''])@{{ record.method }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @{{ record.channel_name }}
                            </p>
                        </div>
                    </div>

                    <!-- Customer, Email, Location Section -->
                    <div class="">
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base text-gray-800 dark:text-white">
                                @{{ record.full_name }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @{{ record.customer_email }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @{{ record.location }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-x-2">
                        <a :href="`{{ route('admin.sales.orders.view', '') }}/${record.id}`">
                            <span class="icon-sort-right rtl:icon-sort-left cursor-pointer p-1.5 text-2xl hover:rounded-md hover:bg-gray-200 dark:hover:bg-gray-800 ltr:ml-1 rtl:mr-1"></span>
                        </a>
                    </div>
                </div>

                <div v-else class="table-responsive grid w-full">
                    <div class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10">
                        <!-- Placeholder Image -->
                        <img
                            src="{{ bagisto_asset('images/empty-placeholders/orders.svg') }}"
                            class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                        />

                        <div class="flex flex-col items-center">
                            <p class="text-base font-semibold text-gray-400">
                                @lang('admin::app.customers.customers.view.datagrid.orders.empty-order')
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-order-status-filter-template"
    >
        <div class="mt-4 mb-4 flex flex-wrap gap-2">
            <button
                @click="filterByStatus(null)"
                :class="[
                    'flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-all duration-200',
                    activeStatus === null
                        ? 'bg-gray-800 text-white shadow-md dark:bg-gray-200 dark:text-gray-800'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                ]"
            >
                <span>Все</span>
                <span
                    :class="[
                        'rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none',
                        activeStatus === null
                            ? 'bg-white/20 text-white dark:bg-gray-800/30 dark:text-gray-800'
                            : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
                    ]"
                >
                    @{{ totalCount }}
                </span>
            </button>

            <button
                v-for="status in statuses"
                :key="status.code"
                @click="filterByStatus(status.code)"
                :class="[
                    'flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-all duration-200',
                    activeStatus === status.code
                        ? 'shadow-md text-white'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                ]"
                :style="activeStatus === status.code ? { backgroundColor: status.color } : {}"
            >
                <span
                    v-if="activeStatus !== status.code"
                    class="h-2 w-2 rounded-full"
                    :style="{ backgroundColor: status.color }"
                ></span>
                <span>@{{ status.name }}</span>
                <span
                    v-if="statusCounts[status.code]"
                    :class="[
                        'rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none',
                        activeStatus === status.code
                            ? 'bg-white/20 text-white'
                            : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
                    ]"
                >
                    @{{ statusCounts[status.code] }}
                </span>
            </button>
        </div>
    </script>

    <script type="module">
        app.component('v-order-status-filter', {
            template: '#v-order-status-filter-template',

            data() {
                return {
                    activeStatus: null,
                    statuses: @json($orderStatuses ?? []),
                    totalCount: {{ count($customer->orders) }},
                    statusCounts: @json($customer->orders->groupBy('status')->map->count()),
                };
            },

            methods: {
                filterByStatus(code) {
                    if (this.activeStatus === code) {
                        return;
                    }

                    this.activeStatus = code;

                    if (code === null) {
                        this.$emitter.emit('datagrid:filter', {
                            columns: [],
                        });
                    } else {
                        this.$emitter.emit('datagrid:filter', {
                            columns: [{
                                index: 'status',
                                value: [code],
                            }],
                        });
                    }
                },
            },
        });
    </script>
@endPushOnce
