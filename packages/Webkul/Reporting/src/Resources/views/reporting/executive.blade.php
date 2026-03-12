<x-admin::layouts>
    <x-slot:title>
        Обзорная панель
    </x-slot>

    <!-- Page Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 4px 15px rgba(99,102,241,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">Обзорная панель</p>
                <p class="text-xs text-gray-400">Ключевые метрики и сводка бизнеса</p>
            </div>
        </div>

        <v-analytics-filters></v-analytics-filters>
    </div>

    <div id="analytics-executive-app">
        <v-executive-dashboard></v-executive-dashboard>
    </div>

    @include('reporting::reporting.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-executive-dashboard-template">
        <div>
            <!-- Loading state -->
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div v-for="i in 8" :key="i" class="shimmer h-32 rounded-xl"></div>
            </div>

            <div v-else>
                <!-- North Star KPI Cards -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
                    <!-- Online Order Share -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-900/30">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                            </div>
                            <div class="flex-1">
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Доля онлайн</span>
                            </div>
                            <span :class="stats.north_star?.online_order_share?.on_track ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400'" class="text-[10px] px-2 py-0.5 rounded-full font-semibold">
                                Цель: 90%
                            </span>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ formatPct(stats.north_star?.online_order_share?.value) }}<span class="text-lg text-gray-400">%</span></p>
                        <div class="mt-2 flex items-center gap-1.5">
                            <span class="inline-block h-1.5 rounded-full bg-indigo-500" :style="{ width: (stats.north_star?.online_order_share?.value || 0) + '%' }"></span>
                            <span class="text-[10px] text-gray-400">@{{ stats.north_star?.online_order_share?.online }}/@{{ stats.north_star?.online_order_share?.total }}</span>
                        </div>
                    </div>

                    <!-- GMV -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Выручка (GMV)</span>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ formatMoney(stats.north_star?.gmv?.value) }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span :class="stats.north_star?.gmv?.change >= 0 ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30' : 'text-red-600 bg-red-50 dark:bg-red-900/30'" class="inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-[11px] font-semibold">
                                <svg v-if="stats.north_star?.gmv?.change >= 0" class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"/></svg>
                                <svg v-else class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"/></svg>
                                @{{ Math.abs(stats.north_star?.gmv?.change || 0) }}%
                            </span>
                            <span class="text-[10px] text-gray-400">vs пред.</span>
                        </div>
                    </div>

                    <!-- AOV -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Средний чек</span>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ formatMoney(stats.north_star?.aov?.value) }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span :class="stats.north_star?.aov?.change >= 0 ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30' : 'text-red-600 bg-red-50 dark:bg-red-900/30'" class="inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-[11px] font-semibold">
                                @{{ stats.north_star?.aov?.change >= 0 ? '+' : '' }}@{{ stats.north_star?.aov?.change || 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Avg Order→Ready -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 dark:bg-violet-900/30">
                                <svg class="h-5 w-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Заказ → Готов</span>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ stats.north_star?.avg_order_ready?.formatted || '—' }}</p>
                        <p class="text-[10px] text-gray-400 mt-2">среднее время приготовления</p>
                    </div>

                    <!-- SLA -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg" :class="stats.north_star?.orders_within_sla?.value >= 80 ? 'bg-emerald-50 dark:bg-emerald-900/30' : 'bg-red-50 dark:bg-red-900/30'">
                                <svg class="h-5 w-5" :class="stats.north_star?.orders_within_sla?.value >= 80 ? 'text-emerald-600' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">В рамках SLA</span>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ formatPct(stats.north_star?.orders_within_sla?.value) }}<span class="text-lg text-gray-400">%</span></p>
                        <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full transition-all" :class="stats.north_star?.orders_within_sla?.value >= 80 ? 'bg-emerald-500' : 'bg-red-500'" :style="{ width: Math.min(stats.north_star?.orders_within_sla?.value || 0, 100) + '%' }"></div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">@{{ stats.north_star?.orders_within_sla?.within }} / @{{ stats.north_star?.orders_within_sla?.total }} заказов</p>
                    </div>

                    <!-- Repeat Rate -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-pink-50 dark:bg-pink-900/30">
                                <svg class="h-5 w-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </div>
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Повторные клиенты</span>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ formatPct(stats.north_star?.repeat_rate?.value) }}<span class="text-lg text-gray-400">%</span></p>
                        <p class="text-[10px] text-gray-400 mt-2">@{{ stats.north_star?.repeat_rate?.repeat_customers }} клиентов вернулись</p>
                    </div>

                    <!-- Payment Success Rate -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-50 dark:bg-cyan-900/30">
                                <svg class="h-5 w-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Успех оплат</span>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ formatPct(stats.payment_rate?.overall_rate) }}<span class="text-lg text-gray-400">%</span></p>
                        <div class="mt-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-cyan-500 transition-all" :style="{ width: Math.min(stats.payment_rate?.overall_rate || 0, 100) + '%' }"></div>
                        </div>
                    </div>

                    <!-- Total Orders -->
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Всего заказов</span>
                        </div>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ stats.north_star?.total_orders }}</p>
                        <p class="text-[10px] text-gray-400 mt-2">за выбранный период</p>
                    </div>
                </div>

                <!-- Second Row: Active Users & Revenue Per User & Channels -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-xs font-semibold text-gray-500 uppercase">Активные пользователи</span>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1 text-center rounded-lg bg-gray-50 dark:bg-gray-700/50 py-3">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">@{{ stats.active_users?.dau }}</p>
                                <p class="text-[10px] font-medium text-gray-400 mt-0.5">DAU</p>
                            </div>
                            <div class="flex-1 text-center rounded-lg bg-gray-50 dark:bg-gray-700/50 py-3">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">@{{ stats.active_users?.wau }}</p>
                                <p class="text-[10px] font-medium text-gray-400 mt-0.5">WAU</p>
                            </div>
                            <div class="flex-1 text-center rounded-lg bg-gray-50 dark:bg-gray-700/50 py-3">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">@{{ stats.active_users?.mau }}</p>
                                <p class="text-[10px] font-medium text-gray-400 mt-0.5">MAU</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <span class="text-xs font-semibold text-gray-500 uppercase">Выручка на пользователя</span>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1 text-center rounded-lg bg-gray-50 dark:bg-gray-700/50 py-3">
                                <p class="text-xl font-bold text-gray-900 dark:text-white">@{{ formatMoney(stats.revenue_per_user?.arpu) }}</p>
                                <p class="text-[10px] font-medium text-gray-400 mt-0.5">ARPU</p>
                            </div>
                            <div class="flex-1 text-center rounded-lg bg-gray-50 dark:bg-gray-700/50 py-3">
                                <p class="text-xl font-bold text-gray-900 dark:text-white">@{{ formatMoney(stats.revenue_per_user?.rppu) }}</p>
                                <p class="text-[10px] font-medium text-gray-400 mt-0.5">RPPU</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="text-xs font-semibold text-gray-500 uppercase">Каналы — Выручка</span>
                        </div>
                        <div class="space-y-2">
                            <div v-for="ch in stats.channel_split" :key="ch.channel_name" class="flex items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-700/50 px-3 py-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">@{{ ch.channel_name }}</span>
                                <div class="text-right">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">@{{ formatMoney(ch.revenue) }}</span>
                                    <span class="text-[10px] text-gray-400 ml-1">(@{{ ch.orders }} зак.)</span>
                                </div>
                            </div>
                            <div v-if="!stats.channel_split?.length" class="text-sm text-gray-400 text-center py-4">Нет данных</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-executive-dashboard', {
            template: '#v-executive-dashboard-template',
            data() {
                return { stats: {}, isLoading: true };
            },
            mounted() {
                this.fetchStats();
                this.$emitter.on('analytics-filter-changed', (filters) => {
                    this.fetchStats(filters);
                });
            },
            methods: {
                fetchStats(filters = {}) {
                    this.isLoading = true;
                    const params = new URLSearchParams({
                        start: filters.start || new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0],
                        end: filters.end || new Date().toISOString().split('T')[0],
                        ...(filters.channel ? { channel: filters.channel } : {}),
                    });
                    fetch(`{{ route('admin.analytics.executive.stats') }}?${params}`)
                        .then(r => r.json())
                        .then(data => { this.stats = data; this.isLoading = false; })
                        .catch(() => { this.isLoading = false; });
                },
                formatMoney(v) { return v != null ? Number(v).toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' ₽' : '—'; },
                formatPct(v) { return v != null ? Number(v).toFixed(1) : '—'; },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>
