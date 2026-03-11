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

    @include('reporting::reporting.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-daily-dashboard-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
                <div v-for="i in 10" :key="i" class="shimmer h-32 rounded-xl"></div>
            </div>

            <div v-else>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5 mb-6">
                    <!-- Online Order Share -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Онлайн</span>
                        </div>
                        <p class="text-2xl font-extrabold" :class="stats.online_order_share?.on_track ? 'text-emerald-600' : 'text-red-500'">
                            @{{ fmt(stats.online_order_share?.value) }}<span class="text-lg">%</span>
                        </p>
                    </div>

                    <!-- GMV -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                                <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Выручка</span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">@{{ money(stats.gmv?.value) }}</p>
                        <div class="mt-1 inline-flex items-center gap-1 rounded-full px-1.5 py-0.5" :class="stats.gmv?.change >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20'">
                            <svg class="h-3 w-3" :class="stats.gmv?.change >= 0 ? 'text-emerald-500' : 'text-red-500 rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"/></svg>
                            <span class="text-[10px] font-semibold" :class="stats.gmv?.change >= 0 ? 'text-emerald-600' : 'text-red-600'">@{{ Math.abs(stats.gmv?.change || 0) }}%</span>
                        </div>
                    </div>

                    <!-- SLA -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                                <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">SLA</span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">@{{ fmt(stats.sla?.value) }}<span class="text-lg text-gray-400">%</span></p>
                        <div class="mt-1.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full transition-all" :class="(stats.sla?.value || 0) >= 90 ? 'bg-emerald-500' : 'bg-amber-500'" :style="{ width: Math.min(stats.sla?.value || 0, 100) + '%' }"></div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">@{{ stats.sla?.within }}/@{{ stats.sla?.total }}</p>
                    </div>

                    <!-- Repeat Rate -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30">
                                <svg class="h-3.5 w-3.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Повторные</span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">@{{ fmt(stats.repeat_rate?.value) }}<span class="text-lg text-gray-400">%</span></p>
                    </div>

                    <!-- Orders/User -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-pink-100 dark:bg-pink-900/30">
                                <svg class="h-3.5 w-3.5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Зак/юзер</span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">@{{ stats.orders_per_user?.orders_per_user }}</p>
                    </div>

                    <!-- AOV -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-100 dark:bg-cyan-900/30">
                                <svg class="h-3.5 w-3.5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Ср. чек</span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">@{{ money(stats.aov?.value) }}</p>
                    </div>

                    <!-- Payment Success -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                                <svg class="h-3.5 w-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Оплаты</span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">@{{ fmt(stats.payment_rate?.overall_rate) }}<span class="text-lg text-gray-400">%</span></p>
                    </div>

                    <!-- Attach Rate -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30">
                                <svg class="h-3.5 w-3.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Допродажа</span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">@{{ fmt(stats.attach_rate?.rate) }}<span class="text-lg text-gray-400">%</span></p>
                    </div>

                    <!-- Complaints -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                                <svg class="h-3.5 w-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Жалобы</span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">@{{ stats.complaints?.total || 0 }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Решено: <span class="font-semibold text-emerald-500">@{{ stats.complaints?.resolved || 0 }}</span></p>
                    </div>

                    <!-- Kiosk Uptime -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-900/30">
                                <svg class="h-3.5 w-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Киоски</span>
                        </div>
                        <div v-if="stats.kiosk_uptime?.length" class="space-y-1.5">
                            <div v-for="k in stats.kiosk_uptime" :key="k.kiosk" class="flex items-center justify-between">
                                <span class="text-xs text-gray-600 dark:text-gray-300">@{{ k.kiosk }}</span>
                                <span class="text-xs font-bold" :class="k.uptime_pct >= 99 ? 'text-emerald-500' : 'text-red-500'">@{{ k.uptime_pct }}%</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400 mt-1">—</p>
                    </div>
                </div>

                <!-- Top Dishes Today -->
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30">
                            <svg class="h-3.5 w-3.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                        </span>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Топ блюда</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <th class="text-left pb-3 pr-4 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">#</th>
                                    <th class="text-left pb-3 pr-4 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Блюдо</th>
                                    <th class="text-right pb-3 pr-4 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Кол-во</th>
                                    <th class="text-right pb-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Выручка</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(dish, idx) in stats.top_dishes" :key="idx" class="border-b border-gray-50 dark:border-gray-700/30 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="py-2.5 pr-4">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold" :class="idx < 3 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'text-gray-400'">@{{ idx + 1 }}</span>
                                    </td>
                                    <td class="py-2.5 pr-4 font-medium text-gray-700 dark:text-white">@{{ dish.name }}</td>
                                    <td class="py-2.5 pr-4 text-right">
                                        <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:text-gray-300">@{{ dish.quantity }}</span>
                                    </td>
                                    <td class="py-2.5 text-right font-bold text-gray-900 dark:text-white">@{{ money(dish.revenue) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-if="!stats.top_dishes?.length" class="text-sm text-gray-400 text-center py-6">Нет данных о блюдах</p>
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
