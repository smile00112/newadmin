<x-admin::layouts>
    <x-slot:title>
        Каналы и форматы
    </x-slot>

    <!-- Page Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 4px 15px rgba(99,102,241,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">Каналы и форматы</p>
                <p class="text-xs text-gray-400">Приложение / Киоск / Касса, Зал vs С собой</p>
            </div>
        </div>

        <v-analytics-filters></v-analytics-filters>
    </div>

    <v-channels-dashboard></v-channels-dashboard>

    @include('reporting::reporting.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-channels-dashboard-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div v-for="i in 6" :key="i" class="shimmer h-40 rounded-xl"></div>
            </div>

            <div v-else class="space-y-6">
                <!-- Channel Split -->
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2 mb-5">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Распределение заказов по каналам</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div v-for="(ch, idx) in stats.channel_split || []" :key="ch.channel || idx" class="relative overflow-hidden rounded-xl p-5 text-center" :style="{ background: channelGradientBg(idx) }">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-300 mb-1">@{{ channelLabel(ch.channel) }}</p>
                            <p class="text-4xl font-extrabold" :style="{ color: channelAccent(idx) }">@{{ ch.orders }}</p>
                            <div class="w-full bg-white/60 dark:bg-gray-700 rounded-full h-2 mt-3">
                                <div class="h-2 rounded-full transition-all" :style="{ width: ch.pct + '%', background: channelGradient(idx) }"></div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2"><span class="font-bold">@{{ ch.pct }}%</span> · @{{ money(ch.revenue) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Dine-In vs Take-away + Location -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Зал vs С собой</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="f in stats.dine_vs_takeaway || []" :key="f.type" class="rounded-xl p-4 text-center" :style="{ background: f.type === 'dine_in' ? 'linear-gradient(135deg, rgba(99,102,241,0.06), rgba(99,102,241,0.12))' : 'linear-gradient(135deg, rgba(245,158,11,0.06), rgba(245,158,11,0.12))' }">
                                <span class="flex h-8 w-8 mx-auto items-center justify-center rounded-lg mb-2" :class="f.type === 'dine_in' ? 'bg-indigo-100 dark:bg-indigo-900/30' : 'bg-amber-100 dark:bg-amber-900/30'">
                                    <svg v-if="f.type === 'dine_in'" class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <svg v-else class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                </span>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-300">@{{ f.type === 'dine_in' ? 'В зале' : 'С собой' }}</p>
                                <p class="text-3xl font-extrabold mt-1" :class="f.type === 'dine_in' ? 'text-indigo-600' : 'text-amber-600'">@{{ f.orders }}</p>
                                <p class="text-xs text-gray-400 mt-1">@{{ f.pct }}%</p>
                            </div>
                        </div>
                        <p v-if="!stats.dine_vs_takeaway?.length" class="text-sm text-gray-400 text-center py-4">Нет данных</p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Заказы по локациям</h3>
                        </div>
                        <div class="space-y-0">
                            <div v-for="loc in stats.orders_by_location || []" :key="loc.location" class="flex items-center justify-between py-2.5 border-b border-gray-50 dark:border-gray-700/30 last:border-0">
                                <span class="text-sm font-medium text-gray-700 dark:text-white">@{{ loc.location || '—' }}</span>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-bold text-gray-600 dark:text-gray-300">@{{ loc.orders }}</span>
                                    <span class="text-xs text-gray-400">@{{ money(loc.revenue) }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-if="!stats.orders_by_location?.length" class="text-sm text-gray-400 text-center py-4">Нет данных</p>
                    </div>
                </div>

                <!-- Revenue by Channel -->
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Выручка по каналам</h3>
                    </div>
                    <div class="space-y-4">
                        <div v-for="(r, idx) in stats.revenue_by_channel || []" :key="r.channel || idx">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">@{{ channelLabel(r.channel) }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">@{{ money(r.revenue) }}</span>
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500">@{{ r.pct }}%</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full transition-all"
                                     :style="{ width: Math.max(r.pct, 3) + '%', background: channelGradient(idx) }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rating & NPS & Complaints -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Rating -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 text-center">
                        <div class="flex items-center justify-center gap-2 mb-3">
                            <svg class="h-4 w-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Средняя оценка</span>
                        </div>
                        <p class="text-5xl font-extrabold" :class="(stats.post_order_rating?.avg || 0) >= 4 ? 'text-emerald-600' : (stats.post_order_rating?.avg || 0) >= 3 ? 'text-amber-500' : 'text-red-500'">
                            @{{ stats.post_order_rating?.avg != null ? Number(stats.post_order_rating.avg).toFixed(1) : '—' }}
                        </p>
                        <div class="flex justify-center mt-2 gap-1">
                            <template v-for="s in 5">
                                <svg class="w-5 h-5" :class="s <= Math.round(stats.post_order_rating?.avg || 0) ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </template>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">@{{ stats.post_order_rating?.rated_orders || 0 }} оценок</p>
                    </div>

                    <!-- NPS -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 text-center">
                        <div class="flex items-center justify-center gap-2 mb-3">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">NPS</span>
                        </div>
                        <div class="relative h-20 w-20 mx-auto">
                            <svg class="h-20 w-20 -rotate-90" viewBox="0 0 36 36">
                                <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" :stroke="(stats.nps?.score ?? 0) >= 50 ? '#10b981' : (stats.nps?.score ?? 0) >= 0 ? '#f59e0b' : '#ef4444'" stroke-width="3"
                                      :stroke-dasharray="Math.min(Math.abs(stats.nps?.score || 0), 100) + ', 100'"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-2xl font-extrabold" :class="(stats.nps?.score ?? 0) >= 50 ? 'text-emerald-600' : (stats.nps?.score ?? 0) >= 0 ? 'text-amber-500' : 'text-red-500'">@{{ stats.nps?.score != null ? stats.nps.score : '—' }}</span>
                        </div>
                        <div class="flex justify-center gap-3 mt-3">
                            <span class="flex items-center gap-1 text-[10px] text-gray-400"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>@{{ stats.nps?.promoters || 0 }}</span>
                            <span class="flex items-center gap-1 text-[10px] text-gray-400"><span class="h-2 w-2 rounded-full bg-gray-400"></span>@{{ stats.nps?.passives || 0 }}</span>
                            <span class="flex items-center gap-1 text-[10px] text-gray-400"><span class="h-2 w-2 rounded-full bg-red-500"></span>@{{ stats.nps?.detractors || 0 }}</span>
                        </div>
                    </div>

                    <!-- Complaints -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Жалобы</span>
                        </div>
                        <p class="text-4xl font-extrabold text-red-500 mb-3">@{{ stats.complaints?.total || 0 }}</p>
                        <div v-if="(stats.complaints?.top_themes || []).length" class="space-y-1.5">
                            <div v-for="th in (stats.complaints?.top_themes || []).slice(0,5)" :key="th.theme" class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400 truncate mr-2">@{{ th.theme }}</span>
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-full px-2 py-0.5">@{{ th.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-channels-dashboard', {
            template: '#v-channels-dashboard-template',
            data() { return { stats: {}, isLoading: true }; },
            mounted() {
                this.fetchStats();
                this.$emitter.on('analytics-filter-changed', (f) => this.fetchStats(f));
            },
            methods: {
                fetchStats(filters = {}) {
                    this.isLoading = true;
                    const params = new URLSearchParams({
                        start: filters.start || new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0],
                        end: filters.end || new Date().toISOString().split('T')[0],
                        ...(filters.channel ? { channel: filters.channel } : {}),
                    });
                    fetch(`{{ route('admin.analytics.channels.stats') }}?${params}`)
                        .then(r => r.json())
                        .then(data => { this.stats = data; this.isLoading = false; })
                        .catch(() => { this.isLoading = false; });
                },
                money(v) { return v != null ? Number(v).toLocaleString('ru-RU', { maximumFractionDigits: 0 }) + ' ₽' : '—'; },
                channelLabel(ch) {
                    if (!ch) return 'Основной';
                    const labels = {
                        'default': 'Основной',
                        'по умолчанию': 'Основной',
                        'app': 'Приложение',
                        'kiosk': 'Киоск',
                        'cashier': 'Касса',
                        'web': 'Сайт',
                    };
                    return labels[(ch || '').toLowerCase()] || ch;
                },
                channelGradient(idx) {
                    const g = [
                        'linear-gradient(135deg, #6366f1, #4f46e5)',
                        'linear-gradient(135deg, #10b981, #059669)',
                        'linear-gradient(135deg, #f59e0b, #d97706)',
                        'linear-gradient(135deg, #ec4899, #db2777)',
                        'linear-gradient(135deg, #8b5cf6, #7c3aed)',
                    ];
                    return g[idx % g.length];
                },
                channelGradientBg(idx) {
                    const g = [
                        'linear-gradient(135deg, rgba(99,102,241,0.06), rgba(99,102,241,0.12))',
                        'linear-gradient(135deg, rgba(16,185,129,0.06), rgba(16,185,129,0.12))',
                        'linear-gradient(135deg, rgba(245,158,11,0.06), rgba(245,158,11,0.12))',
                        'linear-gradient(135deg, rgba(236,72,153,0.06), rgba(236,72,153,0.12))',
                        'linear-gradient(135deg, rgba(139,92,246,0.06), rgba(139,92,246,0.12))',
                    ];
                    return g[idx % g.length];
                },
                channelAccent(idx) {
                    const c = ['#4f46e5', '#059669', '#d97706', '#db2777', '#7c3aed'];
                    return c[idx % c.length];
                },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>
