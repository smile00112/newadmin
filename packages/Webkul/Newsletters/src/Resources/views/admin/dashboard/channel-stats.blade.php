<!-- Channel Stats Vue Component for Dashboard -->
<v-dashboard-channel-stats channel-type="{{ $channelType }}" channel-label="{{ $channelLabel }}">
    <!-- Shimmer -->
    <div class="grid gap-4 border-b px-4 py-2 dark:border-gray-800">
        <div class="shimmer h-4 w-24 rounded"></div>
        <div class="shimmer h-64 w-full rounded"></div>
    </div>
</v-dashboard-channel-stats>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-channel-stats-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <div class="grid gap-4 border-b px-4 py-2 dark:border-gray-800">
                <div class="shimmer h-4 w-24 rounded"></div>
                <div class="shimmer h-64 w-full rounded"></div>
            </div>
        </template>

        <!-- Channel Stats Section -->
        <template v-else>
            <div class="grid gap-4 px-4 py-4 dark:border-gray-800">
                <div class="flex justify-between gap-2">
                    <div class="flex flex-col justify-between gap-1">
                        <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                            @{{ channelLabel }} - @lang('newsletters::app.admin.reports.messages-stats.title')
                        </p>
                    </div>

                    <div class="flex flex-col justify-between gap-1">
                        <!-- Date Range -->
                        <p class="text-right text-sm font-semibold text-gray-400 dark:text-white">
                            @{{ report.date_range }}
                        </p>
                    </div>
                </div>

                <!-- Line Chart -->
                <x-admin::charts.line
                    ::labels="chartLabels"
                    ::datasets="chartDatasets"
                    ::aspect-ratio="2.5"
                />

                <!-- Legend -->
                <div class="flex flex-wrap gap-4 justify-center mt-4">
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-full" style="background-color: #598de6;"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-300">@lang('newsletters::app.admin.reports.messages-stats.sent')</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-full" style="background-color: #10b981;"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-300">@lang('newsletters::app.admin.reports.messages-stats.received')</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-full" style="background-color: #ec4899;"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-300">@lang('newsletters::app.admin.reports.messages-stats.incoming')</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-full" style="background-color: #f59e0b;"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-300">@lang('newsletters::app.admin.reports.messages-stats.read')</span>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-channel-stats', {
            template: '#v-dashboard-channel-stats-template',

            props: {
                channelType: {
                    type: String,
                    required: true
                },
                channelLabel: {
                    type: String,
                    required: true
                }
            },

            data() {
                return {
                    report: [],

                    isLoading: true,
                }
            },

            computed: {
                chartLabels() {
                    if (!this.report.statistics || !this.report.statistics.over_time) {
                        return [];
                    }
                    return this.report.statistics.over_time.map(({ label }) => label);
                },

                chartDatasets() {
                    if (!this.report.statistics || !this.report.statistics.over_time) {
                        return [];
                    }
                    return [
                        {
                            label: '@lang("newsletters::app.admin.reports.messages-stats.sent")',
                            data: this.report.statistics.over_time.map(({ sent }) => sent),
                            borderColor: '#598de6',
                            backgroundColor: 'rgba(89, 141, 230, 0.1)',
                            tension: 0.4,
                        },
                        {
                            label: '@lang("newsletters::app.admin.reports.messages-stats.received")',
                            data: this.report.statistics.over_time.map(({ received }) => received),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                        },
                        {
                            label: '@lang("newsletters::app.admin.reports.messages-stats.incoming")',
                            data: this.report.statistics.over_time.map(({ incoming }) => incoming),
                            borderColor: '#ec4899',
                            backgroundColor: 'rgba(236, 72, 153, 0.1)',
                            tension: 0.4,
                        },
                        {
                            label: '@lang("newsletters::app.admin.reports.messages-stats.read")',
                            data: this.report.statistics.over_time.map(({ read }) => read),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.4,
                        }
                    ];
                }
            },

            mounted() {
                this.getStats({});

                this.$emitter.on('reporting-filter-updated', this.getStats);
            },

            watch: {
                chartLabels() {
                    // Chart will be updated automatically by the line chart component
                },
                chartDatasets: {
                    handler() {
                        // Chart will be updated automatically by the line chart component
                    },
                    deep: true
                }
            },

            methods: {
                getStats(filters) {
                    this.isLoading = true;

                    var filters = Object.assign({}, filters);
                    filters.channel_type = this.channelType;

                    this.$axios.get("{{ route('admin.newsletters.reports.stats') }}", {
                            params: filters
                        })
                        .then(response => {
                            this.report = response.data;

                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.isLoading = false;
                        });
                }
            }
        });
    </script>
@endPushOnce

