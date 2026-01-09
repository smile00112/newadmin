<!-- Channel Lists Stats Vue Component -->
<v-reports-channel-lists-stats channel-type="{{ $channelType }}" channel-label="{{ $channelLabel }}">
    <!-- Shimmer -->
    <div class="grid gap-4 border-b px-4 py-2 dark:border-gray-800">
        <div class="shimmer h-4 w-24 rounded"></div>
        <div class="shimmer h-64 w-full rounded"></div>
    </div>
</v-reports-channel-lists-stats>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-reports-channel-lists-stats-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <div class="grid gap-4 border-b px-4 py-2 dark:border-gray-800">
                <div class="shimmer h-4 w-24 rounded"></div>
                <div class="shimmer h-64 w-full rounded"></div>
            </div>
        </template>

        <!-- Channel Lists Stats Section -->
        <template v-else>
            <div class="grid gap-4 px-4 py-4 dark:border-gray-800">
                <div class="flex justify-between gap-2">
                    <div class="flex flex-col justify-between gap-1">
                        <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                            @{{ channelLabel }} - @lang('newsletters::app.admin.reports.mailing-lists-stats.title')
                        </p>
                    </div>

                    <div class="flex flex-col justify-between gap-1">
                        <!-- Date Range -->
                        <p class="text-right text-sm font-semibold text-gray-400 dark:text-white">
                            @{{ report.date_range }}
                        </p>
                    </div>
                </div>

                <!-- Bar Chart -->
                <x-admin::charts.bar
                    ::labels="chartLabels"
                    ::datasets="chartDatasets"
                    ::aspect-ratio="2.5"
                />
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-reports-channel-lists-stats', {
            template: '#v-reports-channel-lists-stats-template',

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
                    return [{
                        label: this.channelLabel + ' - @lang("newsletters::app.admin.reports.mailing-lists-stats.title")',
                        data: this.report.statistics.over_time.map(({ count }) => count),
                        barThickness: 6,
                        backgroundColor: '#8b5cf6',
                    }];
                }
            },

            mounted() {
                this.getStats({});

                this.$emitter.on('reports-filter-updated', this.getStats);
            },

            watch: {
                chartLabels() {
                    // Chart will be updated automatically by the bar chart component
                },
                chartDatasets: {
                    handler() {
                        // Chart will be updated automatically by the bar chart component
                    },
                    deep: true
                }
            },

            methods: {
                getStats(filters) {
                    this.isLoading = true;

                    var filters = Object.assign({}, filters);
                    filters.type = 'mailing-lists';
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

