<x-admin::layouts>
    <x-slot:title>
        Продуктовая аналитика
    </x-slot>

    <!-- Page Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); box-shadow: 0 4px 15px rgba(139,92,246,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">Продуктовая аналитика</p>
                <p class="text-xs text-gray-400">Воронки, ретенция, поведение пользователей</p>
            </div>
        </div>

        <v-analytics-filters></v-analytics-filters>
    </div>

    <v-product-analytics></v-product-analytics>

    @include('reporting::reporting.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-product-analytics-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div v-for="i in 6" :key="i" class="shimmer h-48 rounded-xl"></div>
            </div>

            <div v-else class="space-y-6">
                <!-- Funnel -->
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2 mb-5">
                        <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Воронка конверсии</h3>
                    </div>
                    <div class="flex items-end gap-3" style="min-height: 200px;">
                        <div v-for="(step, idx) in stats.funnel" :key="idx" class="flex-1 flex flex-col items-center group">
                            <div class="mb-2 text-center">
                                <span class="text-lg font-extrabold text-gray-900 dark:text-white">@{{ step.count }}</span>
                            </div>
                            <div class="w-full rounded-t-lg transition-all duration-300 group-hover:opacity-80"
                                 :style="{ height: barHeight(step.count, stats.funnel), background: funnelGradient(idx) }"></div>
                            <div class="mt-3 text-center">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">@{{ stepLabel(step.step) }}</span>
                                <div v-if="step.dropoff > 0" class="mt-1 inline-flex items-center gap-0.5 rounded-full bg-red-50 dark:bg-red-900/20 px-2 py-0.5">
                                    <svg class="h-3 w-3 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"/></svg>
                                    <span class="text-[11px] font-semibold text-red-600 dark:text-red-400">@{{ step.dropoff }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Arrow connectors -->
                    <div class="flex mt-1 px-4" v-if="stats.funnel?.length > 1">
                        <template v-for="(step, idx) in stats.funnel" :key="'arr'+idx">
                            <div v-if="idx < stats.funnel.length - 1" class="flex-1 flex justify-center">
                                <svg class="h-4 w-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 32 16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 8h24m0 0l-6-6m6 6l-6 6"/></svg>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Session → Order -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Конверсия в заказ</h3>
                        </div>
                        <p class="text-4xl font-extrabold text-indigo-600 mb-3">@{{ fmt(stats.conversion?.overall) }}<span class="text-xl text-indigo-300">%</span></p>
                        <div v-if="stats.conversion?.by_channel?.length" class="space-y-2">
                            <div v-for="ch in stats.conversion.by_channel" :key="ch.channel" class="flex items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-700/50 px-3 py-2">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">@{{ ch.channel || 'Прямой' }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full bg-indigo-500" :style="{ width: Math.min(ch.conversion || 0, 100) + '%' }"></div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 dark:text-white w-12 text-right">@{{ fmt(ch.conversion) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Time to Payment -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Время до оплаты</h3>
                        </div>
                        <p class="text-4xl font-extrabold text-gray-900 dark:text-white mb-1">@{{ stats.time_to_payment?.avg_seconds || 0 }}<span class="text-xl text-gray-400">с</span></p>
                        <div class="mt-3 flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full bg-emerald-500" :style="{ width: (stats.time_to_payment?.under_60s_share || 0) + '%' }"></div>
                            </div>
                            <span class="text-xs font-semibold text-emerald-600">@{{ fmt(stats.time_to_payment?.under_60s_share) }}% &lt;60с</span>
                        </div>
                    </div>

                    <!-- Retention -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Когортная ретенция</h3>
                        </div>
                        <p class="text-xs text-gray-400 mb-4">Когорта: <span class="font-semibold text-gray-600 dark:text-gray-200">@{{ stats.retention?.cohort_size }}</span> пользователей</p>
                        <div class="flex gap-3">
                            <div class="flex-1 text-center rounded-xl py-4" style="background: linear-gradient(135deg, rgba(139,92,246,0.1), rgba(139,92,246,0.05));">
                                <p class="text-2xl font-extrabold text-violet-600">@{{ stats.retention?.d1 || 0 }}%</p>
                                <p class="text-[10px] font-semibold text-gray-400 mt-1">День 1</p>
                            </div>
                            <div class="flex-1 text-center rounded-xl py-4" style="background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(99,102,241,0.05));">
                                <p class="text-2xl font-extrabold text-indigo-600">@{{ stats.retention?.d7 || 0 }}%</p>
                                <p class="text-[10px] font-semibold text-gray-400 mt-1">День 7</p>
                            </div>
                            <div class="flex-1 text-center rounded-xl py-4" style="background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(59,130,246,0.05));">
                                <p class="text-2xl font-extrabold text-blue-600">@{{ stats.retention?.d30 || 0 }}%</p>
                                <p class="text-[10px] font-semibold text-gray-400 mt-1">День 30</p>
                            </div>
                        </div>
                    </div>

                    <!-- Orders per User / ARPU / RPPU -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Частота и выручка</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                                <p class="text-xl font-extrabold text-gray-900 dark:text-white">@{{ stats.orders_per_user?.orders_per_user }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Заказов / юзер</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                                <p class="text-xl font-extrabold text-gray-900 dark:text-white">@{{ stats.median_tbo?.median_days }}<span class="text-sm text-gray-400">д</span></p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Медиана между зак.</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                                <p class="text-xl font-extrabold text-gray-900 dark:text-white">@{{ money(stats.arpu_rppu?.arpu) }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">ARPU</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                                <p class="text-xl font-extrabold text-gray-900 dark:text-white">@{{ money(stats.arpu_rppu?.rppu) }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">RPPU</p>
                            </div>
                        </div>
                    </div>

                    <!-- AOV 1st vs 2nd visit -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Средний чек: 1-й vs 2-й визит</h3>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1 text-center rounded-xl p-4 border-2 border-indigo-100 dark:border-indigo-800">
                                <p class="text-2xl font-extrabold text-indigo-600">@{{ money(stats.aov_by_visit?.first_visit?.aov) }}</p>
                                <p class="text-[10px] text-gray-400 mt-1">1-й визит</p>
                                <p class="text-[10px] text-gray-400">@{{ stats.aov_by_visit?.first_visit?.orders }} зак.</p>
                            </div>
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </div>
                            <div class="flex-1 text-center rounded-xl p-4 border-2 border-emerald-100 dark:border-emerald-800">
                                <p class="text-2xl font-extrabold text-emerald-600">@{{ money(stats.aov_by_visit?.second_visit?.aov) }}</p>
                                <p class="text-[10px] text-gray-400 mt-1">2-й визит</p>
                                <p class="text-[10px] text-gray-400">@{{ stats.aov_by_visit?.second_visit?.orders }} зак.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Repeat Dish Rate -->
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Повторение блюд (2-й визит)</h3>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="relative h-20 w-20">
                                <svg class="h-20 w-20 -rotate-90" viewBox="0 0 36 36">
                                    <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                    <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#f97316" stroke-width="3"
                                          :stroke-dasharray="(stats.repeat_dish_rate?.rate || 0) + ', 100'"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-sm font-extrabold text-gray-900 dark:text-white">@{{ fmt(stats.repeat_dish_rate?.rate) }}%</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-300"><span class="font-bold">@{{ stats.repeat_dish_rate?.repeated }}</span> из <span class="font-bold">@{{ stats.repeat_dish_rate?.total_items }}</span> позиций</p>
                                <p class="text-[10px] text-gray-400 mt-1">повторяются во 2-м заказе</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- First / Second Order Mix -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-bold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">1</span>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Микс первого заказа</h3>
                        </div>
                        <div v-for="(item, idx) in (stats.first_order_mix || []).slice(0,8)" :key="idx" class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-gray-400 w-4">@{{ idx + 1 }}</span>
                                <span class="text-sm text-gray-700 dark:text-gray-200">@{{ item.name }}</span>
                            </div>
                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-full px-2 py-0.5">@{{ item.quantity }} шт</span>
                        </div>
                        <p v-if="!stats.first_order_mix?.length" class="text-sm text-gray-400 text-center py-4">Нет данных</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">2</span>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Микс второго заказа</h3>
                        </div>
                        <div v-for="(item, idx) in (stats.second_order_mix || []).slice(0,8)" :key="idx" class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-gray-400 w-4">@{{ idx + 1 }}</span>
                                <span class="text-sm text-gray-700 dark:text-gray-200">@{{ item.name }}</span>
                            </div>
                            <span class="text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-full px-2 py-0.5">@{{ item.quantity }} шт</span>
                        </div>
                        <p v-if="!stats.second_order_mix?.length" class="text-sm text-gray-400 text-center py-4">Нет данных</p>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-product-analytics', {
            template: '#v-product-analytics-template',
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
                    fetch(`{{ route('admin.analytics.product.stats') }}?${params}`)
                        .then(r => r.json())
                        .then(data => { this.stats = data; this.isLoading = false; })
                        .catch(() => { this.isLoading = false; });
                },
                money(v) { return v != null ? Number(v).toLocaleString('ru-RU', { maximumFractionDigits: 0 }) + ' ₽' : '—'; },
                fmt(v) { return v != null ? Number(v).toFixed(1) : '—'; },
                barHeight(val, funnel) {
                    const max = Math.max(...funnel.map(f => f.count), 1);
                    return Math.max(val / max * 160, 8) + 'px';
                },
                funnelGradient(idx) {
                    const colors = [
                        'linear-gradient(180deg, #818cf8, #6366f1)',
                        'linear-gradient(180deg, #a78bfa, #8b5cf6)',
                        'linear-gradient(180deg, #c084fc, #a855f7)',
                        'linear-gradient(180deg, #f0abfc, #d946ef)',
                        'linear-gradient(180deg, #f9a8d4, #ec4899)',
                    ];
                    return colors[idx] || colors[0];
                },
                stepLabel(s) {
                    const labels = {
                        registered: 'Регистрация',
                        created_cart: 'Корзина',
                        placed_order: 'Заказ',
                        paid: 'Оплачен',
                        completed: 'Завершён'
                    };
                    return labels[s] || s;
                },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>
