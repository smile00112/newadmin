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

    @include('admin::analytics.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-channels-dashboard-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div v-for="i in 6" :key="i" class="shimmer h-40 rounded-lg"></div>
            </div>

            <div v-else class="space-y-6">
                <!-- Channel Split -->
                <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-4">Распределение заказов по каналам</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div v-for="ch in stats.channel_split || []" :key="ch.channel" class="rounded-lg border p-4 dark:border-gray-600 text-center">
                            <span class="text-[11px] text-gray-400 uppercase">@{{ ch.channel || 'N/A' }}</span>
                            <p class="text-3xl font-bold mt-1" :class="channelColor(ch.channel)">@{{ ch.orders }}</p>
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 mt-2">
                                <div class="h-2 rounded-full" :class="channelBg(ch.channel)" :style="{ width: ch.pct + '%' }"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">@{{ ch.pct }}% · @{{ money(ch.revenue) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Dine-In vs Take-away + Location -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Зал vs С собой</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="f in stats.dine_vs_takeaway || []" :key="f.type" class="border rounded-lg p-3 dark:border-gray-600 text-center">
                                <span class="text-xs text-gray-400">@{{ f.type === 'dine_in' ? 'Зал' : 'С собой' }}</span>
                                <p class="text-2xl font-bold mt-1 dark:text-white">@{{ f.orders }}</p>
                                <p class="text-xs text-gray-500">@{{ f.pct }}%</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Заказы по локациям</h3>
                        <table class="w-full text-sm">
                            <thead><tr class="text-xs text-gray-400 border-b dark:border-gray-700">
                                <th class="text-left pb-2">Локация</th>
                                <th class="text-right pb-2">Заказы</th>
                                <th class="text-right pb-2">Выручка</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="loc in stats.orders_by_location || []" :key="loc.location" class="border-b dark:border-gray-700/50">
                                    <td class="py-1.5 dark:text-white">@{{ loc.location || '—' }}</td>
                                    <td class="py-1.5 text-right font-medium dark:text-white">@{{ loc.orders }}</td>
                                    <td class="py-1.5 text-right text-gray-500">@{{ money(loc.revenue) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-if="!stats.orders_by_location?.length" class="text-sm text-gray-400">Нет данных</p>
                    </div>
                </div>

                <!-- Revenue by Channel -->
                <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Выручка по каналам</h3>
                    <div class="space-y-3">
                        <div v-for="r in stats.revenue_by_channel || []" :key="r.channel" class="flex items-center gap-3">
                            <span class="w-20 text-sm dark:text-gray-300 shrink-0 truncate">@{{ r.channel || 'N/A' }}</span>
                            <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-5">
                                <div class="h-5 rounded-full flex items-center pl-2 text-xs font-medium text-white"
                                     :class="channelBg(r.channel)"
                                     :style="{ width: Math.max(r.pct, 5) + '%' }">
                                    @{{ money(r.revenue) }}
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 w-12 text-right">@{{ r.pct }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Rating & NPS & Complaints -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Средняя оценка</span>
                        <p class="text-4xl font-bold mt-2" :class="stats.post_order_rating?.avg >= 4 ? 'text-green-600' : stats.post_order_rating?.avg >= 3 ? 'text-yellow-500' : 'text-red-500'">
                            @{{ stats.post_order_rating?.avg != null ? Number(stats.post_order_rating.avg).toFixed(1) : '—' }}
                        </p>
                        <div class="flex justify-center mt-1 gap-0.5">
                            <template v-for="s in 5">
                                <svg class="w-4 h-4" :class="s <= Math.round(stats.post_order_rating?.avg || 0) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </template>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">@{{ stats.post_order_rating?.rated_orders || 0 }} оценок</p>
                    </div>

                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Индекс лояльности (NPS)</span>
                        <p class="text-4xl font-bold mt-2" :class="(stats.nps?.score ?? 0) >= 50 ? 'text-green-600' : (stats.nps?.score ?? 0) >= 0 ? 'text-yellow-500' : 'text-red-500'">
                            @{{ stats.nps?.score != null ? stats.nps.score : '—' }}
                        </p>
                        <div class="flex justify-center gap-4 mt-2 text-xs text-gray-400">
                            <span>Пром: @{{ stats.nps?.promoters || 0 }}</span>
                            <span>Пасс: @{{ stats.nps?.passives || 0 }}</span>
                            <span>Детр: @{{ stats.nps?.detractors || 0 }}</span>
                        </div>
                    </div>

                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] text-gray-400 uppercase">Жалобы</span>
                        <p class="text-3xl font-bold text-red-500 mt-2">@{{ stats.complaints?.total || 0 }}</p>
                        <div class="mt-3 space-y-1">
                            <div v-for="th in (stats.complaints?.top_themes || []).slice(0,5)" :key="th.theme" class="flex justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400">@{{ th.theme }}</span>
                                <span class="font-medium text-gray-600 dark:text-gray-300">@{{ th.count }}</span>
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
                channelColor(ch) {
                    const m = { app: 'text-blue-600', kiosk: 'text-emerald-600', cashier: 'text-orange-500' };
                    return m[(ch || '').toLowerCase()] || 'text-gray-700 dark:text-white';
                },
                channelBg(ch) {
                    const m = { app: 'bg-blue-500', kiosk: 'bg-emerald-500', cashier: 'bg-orange-500' };
                    return m[(ch || '').toLowerCase()] || 'bg-gray-400';
                },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>
