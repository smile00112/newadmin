<x-admin::layouts>
    <x-slot:title>
        Операционная аналитика
    </x-slot>

    <!-- Page Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 15px rgba(239,68,68,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">Операционная аналитика</p>
                <p class="text-xs text-gray-400">Время этапов, качество, инциденты</p>
            </div>
        </div>

        <v-analytics-filters></v-analytics-filters>
    </div>

    <v-operations-dashboard></v-operations-dashboard>

    @include('admin::analytics.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-operations-dashboard-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div v-for="i in 8" :key="i" class="shimmer h-28 rounded-lg"></div>
            </div>

            <div v-else class="space-y-6">
                <!-- G. Stage Times -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 mb-0">
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Заказ → Принят</span>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">@{{ stats.stage_times?.order_to_accepted?.formatted }}</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Принят → Готов</span>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">@{{ stats.stage_times?.accepted_to_ready?.formatted }}</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Готов → Отдан</span>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2">@{{ stats.stage_times?.ready_to_served?.formatted }}</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Итого (среднее)</span>
                        <p class="text-3xl font-bold text-indigo-600 mt-2">@{{ stats.stage_times?.total_avg?.formatted }}</p>
                    </div>
                </div>

                <!-- Quality Metrics Row -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] text-gray-400 uppercase">Некорректных заказов</span>
                        <p class="text-2xl font-bold text-red-500 mt-1">@{{ fmt(stats.incorrect_orders?.rate) }}%</p>
                        <p class="text-xs text-gray-400">@{{ stats.incorrect_orders?.count }} из @{{ stats.incorrect_orders?.total }}</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] text-gray-400 uppercase">Отмены</span>
                        <p class="text-2xl font-bold text-orange-500 mt-1">@{{ fmt(stats.cancel_refund?.cancel_rate) }}%</p>
                        <p class="text-xs text-gray-400">@{{ stats.cancel_refund?.cancelled }} отмен</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] text-gray-400 uppercase">Возвраты</span>
                        <p class="text-2xl font-bold text-orange-500 mt-1">@{{ fmt(stats.cancel_refund?.refund_rate) }}%</p>
                        <p class="text-xs text-gray-400">@{{ stats.cancel_refund?.refunded }} возвратов</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] text-gray-400 uppercase">Задержка выдачи (среднее)</span>
                        <p class="text-2xl font-bold dark:text-white mt-1">@{{ stats.handoff_delays?.formatted }}</p>
                        <p class="text-xs text-gray-400">max: @{{ stats.handoff_delays?.max_seconds }}с</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-[11px] text-gray-400 uppercase">Сессии без сбоев</span>
                        <p class="text-2xl font-bold text-green-600 mt-1">@{{ fmt(stats.crash_free?.crash_free_rate) }}%</p>
                    </div>
                </div>

                <!-- H. Payments -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Успех оплат по методам</h3>
                        <div v-if="stats.payment_rate?.by_method?.length" class="space-y-2">
                            <div v-for="m in stats.payment_rate.by_method" :key="m.method" class="flex justify-between items-center text-sm">
                                <span class="dark:text-gray-300">@{{ m.method }}</span>
                                <div class="flex gap-3 items-center">
                                    <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full" :class="m.success_rate >= 95 ? 'bg-green-500' : 'bg-yellow-500'" :style="{ width: m.success_rate + '%' }"></div>
                                    </div>
                                    <span class="font-medium dark:text-white text-xs w-14 text-right">@{{ fmt(m.success_rate) }}%</span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400">Нет данных</p>
                    </div>

                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Причины неудачных оплат</h3>
                        <div v-if="stats.fail_reasons?.length" class="space-y-2">
                            <div v-for="r in stats.fail_reasons" :key="r.fail_reason" class="flex justify-between text-sm">
                                <span class="dark:text-gray-300 truncate mr-3">@{{ r.fail_reason }}</span>
                                <span class="font-medium text-red-500">@{{ r.count }}</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400">Нет данных</p>
                    </div>
                </div>

                <!-- NPS & Complaints -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Индекс лояльности (NPS)</h3>
                        <p class="text-4xl font-bold" :class="(stats.nps?.nps || 0) >= 0 ? 'text-green-600' : 'text-red-500'">@{{ stats.nps?.nps || 0 }}</p>
                        <div class="flex gap-4 mt-3 text-xs text-gray-500">
                            <span>Промоутеры: @{{ stats.nps?.promoters }}</span>
                            <span>Пассивные: @{{ stats.nps?.passives }}</span>
                            <span>Детракторы: @{{ stats.nps?.detractors }}</span>
                        </div>
                    </div>

                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Жалобы и обратная связь</h3>
                        <div class="flex gap-6 mb-3">
                            <div><p class="text-2xl font-bold text-red-500">@{{ stats.complaints?.total || 0 }}</p><p class="text-xs text-gray-400">Всего</p></div>
                            <div><p class="text-2xl font-bold text-green-600">@{{ stats.complaints?.resolved || 0 }}</p><p class="text-xs text-gray-400">Решено</p></div>
                            <div><p class="text-2xl font-bold dark:text-white">@{{ stats.complaints?.avg_resolution_min || 0 }}мин</p><p class="text-xs text-gray-400">Ср. время решения</p></div>
                        </div>
                        <div v-if="stats.complaints?.top_themes?.length" class="mt-2">
                            <p class="text-xs text-gray-400 mb-1">Топ темы:</p>
                            <div v-for="t in stats.complaints.top_themes.slice(0,5)" :key="t.feedback_theme" class="flex justify-between text-xs py-0.5">
                                <span class="dark:text-gray-300">@{{ t.feedback_theme }}</span>
                                <span class="text-gray-500">@{{ t.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Heatmap -->
                <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Тепловая карта заказов (день недели × час)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr>
                                    <th class="text-left pb-2 text-gray-400">Час →</th>
                                    <th v-for="h in 24" :key="h" class="text-center pb-2 text-gray-400 px-0.5">@{{ h - 1 }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="day in 7" :key="day">
                                    <td class="pr-2 py-0.5 text-gray-500 whitespace-nowrap">@{{ dayName(day) }}</td>
                                    <td v-for="h in 24" :key="h" class="px-0.5 py-0.5">
                                        <div class="w-full h-5 rounded-sm" :style="{ backgroundColor: heatColor(heatVal(day, h - 1)) }" :title="heatVal(day, h - 1) + ' заказов'"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Kiosk Uptime -->
                <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800" v-if="stats.kiosk_uptime?.length">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Доступность киосков</h3>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div v-for="k in stats.kiosk_uptime" :key="k.kiosk" class="border rounded-lg p-3 dark:border-gray-600">
                            <p class="text-sm font-medium dark:text-white">@{{ k.kiosk }}</p>
                            <p class="text-xs text-gray-400">@{{ k.location }}</p>
                            <p class="text-lg font-bold mt-1" :class="k.uptime_pct >= 99 ? 'text-green-600' : 'text-red-500'">@{{ k.uptime_pct }}%</p>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full" :class="{'bg-green-100 text-green-700': k.status === 'online', 'bg-red-100 text-red-700': k.status === 'offline', 'bg-yellow-100 text-yellow-700': k.status === 'degraded'}">@{{ k.status }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-operations-dashboard', {
            template: '#v-operations-dashboard-template',
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
                    fetch(`{{ route('admin.analytics.operations.stats') }}?${params}`)
                        .then(r => r.json())
                        .then(data => { this.stats = data; this.isLoading = false; })
                        .catch(() => { this.isLoading = false; });
                },
                fmt(v) { return v != null ? Number(v).toFixed(1) : '—'; },
                dayName(d) { return ['', 'Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'][d]; },
                heatVal(day, hour) {
                    if (!this.stats.heatmap) return 0;
                    const cell = this.stats.heatmap.find(h => h.day_of_week == day && h.hour == hour);
                    return cell ? cell.orders : 0;
                },
                heatColor(val) {
                    if (!this.stats.heatmap || !val) return 'rgba(99,102,241,0.05)';
                    const max = Math.max(...this.stats.heatmap.map(h => h.orders), 1);
                    const intensity = val / max;
                    return `rgba(99,102,241,${0.1 + intensity * 0.85})`;
                },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>
