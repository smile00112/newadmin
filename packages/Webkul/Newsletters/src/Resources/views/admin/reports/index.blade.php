<x-admin::layouts>
    <x-slot:title>
        @lang('newsletters::app.admin.reports.title')
    </x-slot>

    <!-- Page Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold !leading-normal text-gray-800 dark:text-white">
                @lang('newsletters::app.admin.reports.title')
            </p>

            <p class="!leading-normal text-gray-600 dark:text-gray-300">
                @lang('newsletters::app.admin.reports.description')
            </p>
        </div>

        <!-- Filters -->
        <v-reports-filters>
            <!-- Shimmer -->
            <div class="flex gap-1.5">
                <div class="shimmer h-[39px] w-[140px] rounded-md"></div>
                <div class="shimmer h-[39px] w-[140px] rounded-md"></div>
            </div>
        </v-reports-filters>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Messages Statistics Chart -->
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            @include('newsletters::admin.reports.messages-stats')
        </div>

        <!-- Mailing Lists Statistics Chart -->
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            @include('newsletters::admin.reports.mailing-lists-stats')
        </div>
    </div>

    @pushOnce('scripts')
        <script
            type="module"
            src="{{ bagisto_asset('js/chart.js') }}"
        >
        </script>

        <script
            type="text/x-template"
            id="v-reports-filters-template"
        >
            <div class="flex gap-1.5">
                <x-admin::flat-picker.date class="!w-[140px]" ::allow-input="false">
                    <input
                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                        v-model="filters.start"
                        placeholder="@lang('admin::app.dashboard.index.start-date')"
                    />
                </x-admin::flat-picker.date>

                <x-admin::flat-picker.date class="!w-[140px]" ::allow-input="false">
                    <input
                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                        v-model="filters.end"
                        placeholder="@lang('admin::app.dashboard.index.end-date')"
                    />
                </x-admin::flat-picker.date>
            </div>
        </script>

        <script type="module">
            app.component('v-reports-filters', {
                template: '#v-reports-filters-template',

                data() {
                    return {
                        filters: {
                            start: "{{ $startDate->format('Y-m-d') }}",
                            end: "{{ $endDate->format('Y-m-d') }}",
                        }
                    }
                },

                watch: {
                    filters: {
                        handler() {
                            this.$emitter.emit('reports-filter-updated', this.filters);
                        },
                        deep: true
                    }
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

