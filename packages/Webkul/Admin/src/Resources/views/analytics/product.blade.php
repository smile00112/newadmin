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

    @include('admin::analytics.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-product-analytics-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div v-for="i in 6" :key="i" class="shimmer h-48 rounded-lg"></div>
            </div>

            <div v-else class="space-y-6">
                <!-- B. Funnel -->
                <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-4">Воронка конверсии</h3>
                    <div class="flex items-end gap-2 h-40">
                        <div v-for="(step, idx) in stats.funnel" :key="idx" class="flex-1 flex flex-col items-center">
                            <span class="text-xs font-medium dark:text-white mb-1">@{{ step.count }}</span>
                            <div class="w-full rounded-t bg-indigo-500" :style="{ height: barHeight(step.count, stats.funnel) }"></div>
                            <span class="text-[10px] text-gray-400 mt-1 text-center">@{{ stepLabel(step.step) }}</span>
                            <span v-if="step.dropoff > 0" class="text-[10px] text-red-400 font-medium">-@{{ step.dropoff }}%</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Session → Order -->
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Сессия → Заказ</h3>
                        <p class="text-3xl font-bold text-indigo-600 mb-2">@{{ fmt(stats.conversion?.overall) }}%</p>
                        <div v-if="stats.conversion?.by_channel?.length" class="space-y-1">
                            <div v-for="ch in stats.conversion.by_channel" :key="ch.channel" class="flex justify-between text-xs dark:text-gray-300">
                                <span>@{{ ch.channel || 'direct' }}</span>
                                <span class="font-medium">@{{ fmt(ch.conversion) }}% (@{{ ch.orders }}/@{{ ch.sessions }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Time to Payment -->
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Время до оплаты</h3>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white mb-1">@{{ stats.time_to_payment?.avg_seconds || 0 }}с</p>
                        <p class="text-sm text-gray-500">Доля <60с: <span class="font-semibold text-green-600">@{{ fmt(stats.time_to_payment?.under_60s_share) }}%</span></p>
                    </div>

                    <!-- C. Retention -->
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Когортная ретенция</h3>
                        <p class="text-xs text-gray-400 mb-3">Когорта: @{{ stats.retention?.cohort_size }} пользователей</p>
                        <div class="flex gap-6">
                            <div class="text-center">
                                <p class="text-2xl font-bold dark:text-white">@{{ stats.retention?.d1 || 0 }}%</p>
                                <p class="text-xs text-gray-400">D1</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold dark:text-white">@{{ stats.retention?.d7 || 0 }}%</p>
                                <p class="text-xs text-gray-400">D7</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold dark:text-white">@{{ stats.retention?.d30 || 0 }}%</p>
                                <p class="text-xs text-gray-400">D30</p>
                            </div>
                        </div>
                    </div>

                    <!-- Orders per User / ARPU / RPPU -->
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Частота и выручка</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><p class="text-lg font-bold dark:text-white">@{{ stats.orders_per_user?.orders_per_user }}</p><p class="text-xs text-gray-400">Заказов/юзер</p></div>
                            <div><p class="text-lg font-bold dark:text-white">@{{ stats.median_tbo?.median_days }}д</p><p class="text-xs text-gray-400">Медиана между заказами</p></div>
                            <div><p class="text-lg font-bold dark:text-white">@{{ money(stats.arpu_rppu?.arpu) }}</p><p class="text-xs text-gray-400">ARPU</p></div>
                            <div><p class="text-lg font-bold dark:text-white">@{{ money(stats.arpu_rppu?.rppu) }}</p><p class="text-xs text-gray-400">RPPU</p></div>
                        </div>
                    </div>

                    <!-- D. AOV 1st vs 2nd visit -->
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">AOV: первый vs второй визит</h3>
                        <div class="flex gap-8">
                            <div>
                                <p class="text-2xl font-bold text-indigo-600">@{{ money(stats.aov_by_visit?.first_visit?.aov) }}</p>
                                <p class="text-xs text-gray-400">1-й визит (@{{ stats.aov_by_visit?.first_visit?.orders }} зак.)</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-emerald-600">@{{ money(stats.aov_by_visit?.second_visit?.aov) }}</p>
                                <p class="text-xs text-gray-400">2-й визит (@{{ stats.aov_by_visit?.second_visit?.orders }} зак.)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Repeat Dish Rate -->
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Повторение блюд (2-й визит)</h3>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white">@{{ fmt(stats.repeat_dish_rate?.rate) }}%</p>
                        <p class="text-xs text-gray-400 mt-1">@{{ stats.repeat_dish_rate?.repeated }} / @{{ stats.repeat_dish_rate?.total_items }} позиций</p>
                    </div>
                </div>

                <!-- First / Second Order Mix -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Микс первого заказа</h3>
                        <div v-for="(item, idx) in (stats.first_order_mix || []).slice(0,8)" :key="idx" class="flex justify-between text-sm py-1 border-b dark:border-gray-700/50">
                            <span class="dark:text-gray-300">@{{ item.name }}</span>
                            <span class="text-gray-500">@{{ item.quantity }} шт</span>
                        </div>
                    </div>
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Микс второго заказа</h3>
                        <div v-for="(item, idx) in (stats.second_order_mix || []).slice(0,8)" :key="idx" class="flex justify-between text-sm py-1 border-b dark:border-gray-700/50">
                            <span class="dark:text-gray-300">@{{ item.name }}</span>
                            <span class="text-gray-500">@{{ item.quantity }} шт</span>
                        </div>
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
                    return (val / max * 120) + 'px';
                },
                stepLabel(s) {
                    const labels = { menu_viewed: 'Меню', cart_opened: 'Корзина', payment_started: 'Оплата', payment_completed: 'Успех' };
                    return labels[s] || s;
                },
            },
        });
    </script>
    @endPushOnce
</x-admin::layouts>
