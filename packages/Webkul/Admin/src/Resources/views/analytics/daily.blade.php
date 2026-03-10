<x-admin::layouts>
    <x-slot:title>
        Ежедневная сводка
    </x-slot>

    <!-- Page Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 15px rgba(245,158,11,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">Ежедневная сводка</p>
                <p class="text-xs text-gray-400">Ключевые метрики для менеджера</p>
            </div>
        </div>

        <v-analytics-filters></v-analytics-filters>
    </div>

    <div id="analytics-daily-app">
        <v-daily-dashboard></v-daily-dashboard>
    </div>

    @include('admin::analytics.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-daily-dashboard-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div v-for="i in 10" :key="i" class="shimmer h-28 rounded-lg"></div>
            </div>

            <div v-else>
                <!-- K. Daily Management Metrics -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5 mb-6">
                    <!-- Online Order Share -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Доля онлайн-заказов</span>
                        <p class="text-2xl font-bold mt-1" :class="stats.online_order_share?.on_track ? 'text-green-600' : 'text-red-500'">
                            @{{ fmt(stats.online_order_share?.value) }}%
                        </p>
                    </div>

                    <!-- GMV -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Общая выручка</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">@{{ money(stats.gmv?.value) }}</p>
                        <p class="text-xs mt-1" :class="stats.gmv?.change >= 0 ? 'text-green-500' : 'text-red-500'">
                            @{{ stats.gmv?.change >= 0 ? '↑' : '↓' }} @{{ Math.abs(stats.gmv?.change || 0) }}% vs пред. период
                        </p>
                    </div>

                    <!-- SLA -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Заказы в SLA</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">@{{ fmt(stats.sla?.value) }}%</p>
                        <p class="text-xs text-gray-400 mt-1">@{{ stats.sla?.within }}/@{{ stats.sla?.total }}</p>
                    </div>

                    <!-- Repeat Rate -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Доля повторных клиентов</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">@{{ fmt(stats.repeat_rate?.value) }}%</p>
                    </div>

                    <!-- Orders/User -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Заказов на юзера</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">@{{ stats.orders_per_user?.orders_per_user }}</p>
                    </div>

                    <!-- AOV -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Средний чек</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">@{{ money(stats.aov?.value) }}</p>
                    </div>

                    <!-- Payment Success -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Успех оплат</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">@{{ fmt(stats.payment_rate?.overall_rate) }}%</p>
                    </div>

                    <!-- Attach Rate -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Допродажа (напитки)</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">@{{ fmt(stats.attach_rate?.rate) }}%</p>
                    </div>

                    <!-- Complaints -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Инциденты / Жалобы</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">@{{ stats.complaints?.total || 0 }}</p>
                        <p class="text-xs text-gray-400 mt-1">Решено: @{{ stats.complaints?.resolved || 0 }}</p>
                    </div>

                    <!-- Kiosk Uptime -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] font-medium text-gray-400 uppercase">Доступность киосков</span>
                        <div v-if="stats.kiosk_uptime?.length" class="mt-2 space-y-1">
                            <div v-for="k in stats.kiosk_uptime" :key="k.kiosk" class="flex justify-between text-xs">
                                <span class="dark:text-gray-300">@{{ k.kiosk }}</span>
                                <span :class="k.uptime_pct >= 99 ? 'text-green-500' : 'text-red-500'" class="font-medium">@{{ k.uptime_pct }}%</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400 mt-2">—</p>
                    </div>
                </div>

                <!-- Top Dishes Today -->
                <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">🔥 Топ блюда</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="text-xs text-gray-400 border-b dark:border-gray-700">
                                <th class="text-left pb-2 pr-4">#</th>
                                <th class="text-left pb-2 pr-4">Блюдо</th>
                                <th class="text-right pb-2 pr-4">Кол-во</th>
                                <th class="text-right pb-2">Выручка</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="(dish, idx) in stats.top_dishes" :key="idx" class="border-b dark:border-gray-700/50">
                                    <td class="py-2 pr-4 text-gray-400">@{{ idx + 1 }}</td>
                                    <td class="py-2 pr-4 dark:text-white">@{{ dish.name }}</td>
                                    <td class="py-2 pr-4 text-right dark:text-gray-300">@{{ dish.quantity }}</td>
                                    <td class="py-2 text-right font-medium dark:text-white">@{{ money(dish.revenue) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-daily-dashboard', {
            template: '#v-daily-dashboard-template',
            data() {
                return { stats: {}, isLoading: true };
            },
            mounted() {
                this.fetchStats();
                this.$emitter.on('analytics-filter-changed', (f) => this.fetchStats(f));
            },
            methods: {
                fetchStats(filters = {}) {
                    this.isLoading = true;
                    const params = new URLSearchParams({
                        start: filters.start || new Date().toISOString().split('T')[0],
                        end: filters.end || new Date().toISOString().split('T')[0],
                        ...(filters.channel ? { channel: filters.channel } : {}),
                    });
                    fetch(`{{ route('admin.analytics.daily.stats') }}?${params}`)
                        .then(r => r.json())
                        .then(data => { this.stats = data; this.isLoading = false; })
                        .catch(() => { this.isLoading = false; });
                },
                money(v) { return v != null ? Number(v).toLocaleString('ru-RU', { maximumFractionDigits: 0 }) + ' ₽' : '—'; },
                fmt(v) { return v != null ? Number(v).toFixed(1) : '—'; },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>
