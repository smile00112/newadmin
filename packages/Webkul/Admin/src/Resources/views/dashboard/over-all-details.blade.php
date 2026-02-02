<!-- Over Details Vue Component -->
<v-dashboard-overall-details>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.over-all-details />
</v-dashboard-overall-details>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-overall-details-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.over-all-details />
        </template>

        <!-- Total Sales Section -->
        <template v-else>
            <div class="rounded-2xl bg-white p-6 shadow-card transition-shadow duration-300 hover:shadow-card-hover dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                <div class="flex flex-wrap gap-5">
                    <!-- Total Sales -->
                    <div class="flex min-w-[220px] flex-1 gap-4 p-4 rounded-2xl bg-gradient-to-br from-violet-50 via-purple-50 to-fuchsia-50 dark:from-violet-900/30 dark:via-purple-900/20 dark:to-fuchsia-900/20 transition-all duration-300 hover:shadow-lg hover:shadow-violet-500/10 hover:-translate-y-1 border border-violet-100/50 dark:border-violet-800/30">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 via-purple-500 to-fuchsia-500 shadow-lg shadow-violet-500/30 ring-4 ring-violet-500/10">
                            <img
                                src="{{ bagisto_asset('images/total-sales.svg')}}"
                                class="h-7 w-7 brightness-0 invert"
                                title="@lang('admin::app.dashboard.index.total-sales')"
                            >
                        </div>

                        <!-- Sales Stats -->
                        <div class="grid place-content-start gap-1.5">
                            <p class="text-xl font-bold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_sales.formatted_total }}
                            </p>

                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                @lang('admin::app.dashboard.index.total-sales')
                            </p>

                            <!-- Sales Percentage -->
                            <div class="flex items-center gap-1">
                                <span
                                    class="text-lg"
                                    :class="[report.statistics.total_sales.progress < 0 ? 'icon-down-stat text-rose-500' : 'icon-up-stat text-emerald-500']"
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

                    <!-- Total Orders -->
                    <div class="flex min-w-[220px] flex-1 gap-4 p-4 rounded-2xl bg-gradient-to-br from-sky-50 via-cyan-50 to-blue-50 dark:from-sky-900/30 dark:via-cyan-900/20 dark:to-blue-900/20 transition-all duration-300 hover:shadow-lg hover:shadow-sky-500/10 hover:-translate-y-1 border border-sky-100/50 dark:border-sky-800/30">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 via-cyan-500 to-blue-500 shadow-lg shadow-sky-500/30 ring-4 ring-sky-500/10">
                            <img
                                src="{{ bagisto_asset('images/total-orders.svg')}}"
                                class="h-7 w-7 brightness-0 invert"
                                title="@lang('admin::app.dashboard.index.total-orders')"
                            >
                        </div>

                        <!-- Orders Stats -->
                        <div class="grid place-content-start gap-1.5">
                            <p class="text-xl font-bold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_orders.current }}
                            </p>

                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                @lang('admin::app.dashboard.index.total-orders')
                            </p>

                            <!-- Order Percentage -->
                            <div class="flex items-center gap-1">
                                <span
                                    class="text-lg"
                                    :class="[report.statistics.total_orders.progress < 0 ? 'icon-down-stat text-rose-500' : 'icon-up-stat text-emerald-500']"
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

                    <!-- Total Customers -->
                    <div class="flex min-w-[220px] flex-1 gap-4 p-4 rounded-2xl bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 dark:from-emerald-900/30 dark:via-green-900/20 dark:to-teal-900/20 transition-all duration-300 hover:shadow-lg hover:shadow-emerald-500/10 hover:-translate-y-1 border border-emerald-100/50 dark:border-emerald-800/30">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 via-green-500 to-teal-500 shadow-lg shadow-emerald-500/30 ring-4 ring-emerald-500/10">
                            <img
                                src="{{ bagisto_asset('images/customers.svg')}}"
                                class="h-7 w-7 brightness-0 invert"
                                title="@lang('admin::app.dashboard.index.total-customers')"
                            >
                        </div>

                        <!-- Customers Stats -->
                        <div class="grid place-content-start gap-1.5">
                            <p class="text-xl font-bold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_customers.current }}
                            </p>

                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                @lang('admin::app.dashboard.index.total-customers')
                            </p>

                            <!-- Customers Percentage -->
                            <div class="flex items-center gap-1">
                                <span
                                    class="text-lg"
                                    :class="[report.statistics.total_customers.progress < 0 ? 'icon-down-stat text-rose-500' : 'icon-up-stat text-emerald-500']"
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

                    <!-- Average sales -->
                    <div class="flex min-w-[220px] flex-1 gap-4 p-4 rounded-2xl bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 dark:from-amber-900/30 dark:via-yellow-900/20 dark:to-orange-900/20 transition-all duration-300 hover:shadow-lg hover:shadow-amber-500/10 hover:-translate-y-1 border border-amber-100/50 dark:border-amber-800/30">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 via-yellow-500 to-orange-500 shadow-lg shadow-amber-500/30 ring-4 ring-amber-500/10">
                            <img
                                src="{{ bagisto_asset('images/average-orders.svg')}}"
                                class="h-7 w-7 brightness-0 invert"
                                title="@lang('admin::app.dashboard.index.average-sale')"
                            >
                        </div>

                        <!-- Sales Stats -->
                        <div class="grid place-content-start gap-1.5">
                            <p class="text-xl font-bold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.avg_sales.formatted_total }}
                            </p>

                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                @lang('admin::app.dashboard.index.average-sale')
                            </p>

                            <!-- Sales Percentage -->
                            <div class="flex items-center gap-1">
                                <span
                                    class="text-lg"
                                    :class="[report.statistics.avg_sales.progress < 0 ? 'icon-down-stat text-rose-500' : 'icon-up-stat text-emerald-500']"
                                ></span>

                                <p
                                    class="text-sm font-semibold"
                                    :class="[report.statistics.avg_sales.progress < 0 ?  'text-rose-500' : 'text-emerald-500']"
                                >
                                    @{{ Math.abs(report.statistics.avg_sales.progress).toFixed(2) }}%
                                </p>

                            </div>
                        </div>
                    </div>

                    <!-- Unpaid Invoices -->
                    <div class="flex min-w-[220px] flex-1 gap-4 p-4 rounded-2xl bg-gradient-to-br from-rose-50 via-pink-50 to-red-50 dark:from-rose-900/30 dark:via-pink-900/20 dark:to-red-900/20 transition-all duration-300 hover:shadow-lg hover:shadow-rose-500/10 hover:-translate-y-1 border border-rose-100/50 dark:border-rose-800/30">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 via-pink-500 to-red-500 shadow-lg shadow-rose-500/30 ring-4 ring-rose-500/10">
                            <img
                                src="{{ bagisto_asset('images/unpaid-invoices.svg')}}"
                                class="h-7 w-7 brightness-0 invert"
                                title="@lang('admin::app.dashboard.index.total-unpaid-invoices')"
                            >
                        </div>

                        <div class="grid place-content-start gap-1.5">
                            <p class="text-xl font-bold leading-none text-gray-800 dark:text-white">
                                @{{ report.statistics.total_unpaid_invoices.formatted_total }}
                            </p>

                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                @lang('admin::app.dashboard.index.total-unpaid-invoices')
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-overall-details', {
            template: '#v-dashboard-overall-details-template',

            data() {
                return {
                    report: [],

                    isLoading: true,
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

                    filters.type = 'over-all';

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