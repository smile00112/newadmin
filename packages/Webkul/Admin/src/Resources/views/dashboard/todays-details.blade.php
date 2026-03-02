<!-- Todays Details Vue Component -->
<v-dashboard-todays-details>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.todays-details />
</v-dashboard-todays-details>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-todays-details-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.todays-details />
        </template>

        <!-- Total Sales Section -->
        <template v-else>
            <div class="rounded-2xl shadow-card border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="flex flex-wrap gap-5 border-b bg-gradient-to-br from-white to-gray-50/50 p-5 dark:border-gray-800 dark:from-gray-900 dark:to-gray-950/50">
                    <!-- Today's Sales -->
                    <div class="flex min-w-[200px] flex-1 gap-3 p-3 rounded-xl transition-all duration-300 hover:bg-white/80 dark:hover:bg-gray-800/50">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-md shadow-violet-500/25">
                            <img
                                src="{{ bagisto_asset('images/total-sales.svg')}}"
                                class="h-6 w-6 brightness-0 invert"
                                title="@lang('admin::app.dashboard.index.today-sales')"
                            >
                        </div>

                        <!-- Sales Stats -->
                        <div class="grid place-content-start gap-1.5">
                            <p class="text-lg font-bold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_sales.formatted_total }}
                            </p>

                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                @lang('admin::app.dashboard.index.today-sales')
                            </p>

                            <!-- Percentage Of Sales -->
                            <div class="flex items-center gap-1">
                                <span
                                    class="text-base"
                                    :class="[report.statistics.total_sales.progress < 0 ? 'icon-down-stat text-rose-500 dark:!text-rose-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-sm font-semibold"
                                    :class="[report.statistics.total_sales.progress < 0 ?  'text-rose-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.total_sales.progress).toFixed(2) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Orders -->
                    <div class="flex min-w-[200px] flex-1 gap-3 p-3 rounded-xl transition-all duration-300 hover:bg-white/80 dark:hover:bg-gray-800/50">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 shadow-md shadow-sky-500/25">
                            <img
                                src="{{ bagisto_asset('images/total-orders.svg')}}"
                                class="h-6 w-6 brightness-0 invert"
                                title="@lang('admin::app.dashboard.index.today-orders')"
                            >
                        </div>

                        <!-- Orders Stats -->
                        <div class="grid place-content-start gap-1.5">
                            <p class="text-lg font-bold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_orders.current }}
                            </p>

                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                @lang('admin::app.dashboard.index.today-orders')
                            </p>

                            <!-- Orders Percentage -->
                            <div class="flex items-center gap-1">
                                <span
                                    class="text-base"
                                    :class="[report.statistics.total_orders.progress < 0 ? 'icon-down-stat text-rose-500 dark:!text-rose-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-sm font-semibold"
                                    :class="[report.statistics.total_orders.progress < 0 ?  'text-rose-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.total_orders.progress).toFixed(2) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Customers -->
                    <div class="flex min-w-[200px] flex-1 gap-3 p-3 rounded-xl transition-all duration-300 hover:bg-white/80 dark:hover:bg-gray-800/50">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md shadow-emerald-500/25">
                            <img
                                src="{{ bagisto_asset('images/customers.svg')}}"
                                class="h-6 w-6 brightness-0 invert"
                                title="@lang('admin::app.dashboard.index.today-customers')"
                            >
                        </div>

                        <!-- Customers Stats -->
                        <div class="grid place-content-start gap-1.5">
                            <p class="text-lg font-bold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_customers.current }}
                            </p>

                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                @lang('admin::app.dashboard.index.today-customers')
                            </p>

                            <!-- Customers Percentage -->
                            <div class="flex items-center gap-1">
                                <span
                                    class="text-base"
                                    :class="[report.statistics.total_customers.progress < 0 ? 'icon-down-stat text-rose-500 dark:!text-rose-500' : 'icon-up-stat text-emerald-500 dark:!text-emerald-500']"
                                ></span>

                                <p
                                    class="text-sm font-semibold"
                                    :class="[report.statistics.total_customers.progress < 0 ?  'text-rose-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.total_customers.progress).toFixed(2) }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today Orders Details -->
                <div 
                    v-for="order in report.statistics.orders"
                    class="border-b bg-white p-5 transition-all duration-300 hover:bg-gradient-to-r hover:from-gray-50/50 hover:to-transparent dark:border-gray-800 dark:bg-gray-900 dark:hover:from-gray-800/30 dark:hover:to-transparent"
                >
                    <div class="flex flex-wrap gap-4">
                        <!-- Total Sales -->
                        <div class="flex min-w-[180px] flex-1 gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <!-- Order Id -->
                                <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                    @{{ translations.orderId.replace(':replace', order.increment_id) }}
                                </p>
    
                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ order.created_at}}
                                </p>
    
                                <!-- Order Status -->
                                <p :class="'label-' + order.status">
                                    @{{ order.status_label }}
                                </p>
                            </div>
                        </div>

                        <div class="flex min-w-[180px] flex-1 gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                                    @{{ order.formatted_base_grand_total }}
                                </p>
        
                                <!-- Payment Mode -->
                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ order.payment_method }}
                                </p>
        
                                <!-- Channel Name -->
                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ order.channel_name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex min-w-[200px] flex-1 gap-2.5">
                            <div class="flex flex-col gap-1.5">
                            <!-- Customer Details -->
                                <p class="text-base text-gray-800 dark:text-white">
                                    @{{ order.customer_name }}
                                </p>
        
                                <p class="max-w-[180px] break-words text-gray-600 dark:text-gray-300">
                                    @{{ order.customer_email }}
                                </p>
        
                                <!-- Order Address -->
                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ order.billing_address }}
                                </p>
                            </div>
                        </div>
 
                        <div class="flex min-w-[180px] flex-1 items-center justify-between gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <!-- Ordered Product Images -->
                                <div
                                    class="flex flex-wrap gap-1.5"
                                    v-html="order.items"
                                >
                                </div>
                            </div>

                             <!-- View More Icon -->
                             <a :href="'{{ route('admin.sales.orders.view', ':replace') }}'.replace(':replace', order.id)">
                                <span class="icon-sort-right rtl:icon-sort-left cursor-pointer p-1.5 text-2xl hover:rounded-md hover:bg-gray-200 dark:hover:bg-gray-800 ltr:ml-1 rtl:mr-1"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-todays-details', {
            template: '#v-dashboard-todays-details-template',

            data() {
                return {
                    report: [],

                    isLoading: true,

                    translations: {
                        orderId: @json(__('admin::app.dashboard.index.order-id', ['id' => ':replace'])),
                    },
                }
            },

            mounted() {
                this.getStats({});

                this.$emitter.on('reporting-filter-updated', this.getStats);
            },

            methods: {
                getStats(filters) {
                    this.isLoading = true;

                    var filters = Object.assign({}, filters);

                    filters.type = 'today';

                    this.$axios.get("{{ route('admin.dashboard.stats') }}", {
                            params: filters
                        })
                        .then(response => {
                            this.report = response.data;

                            this.isLoading = false;
                        })
                        .catch(error => {});
                }
            }
        });
    </script>
@endPushOnce