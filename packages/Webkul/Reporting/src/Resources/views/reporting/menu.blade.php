<x-admin::layouts>
    <x-slot:title>
        Аналитика меню
    </x-slot>

    <!-- Page Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 15px rgba(16,185,129,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">Аналитика меню</p>
                <p class="text-xs text-gray-400">Блюда, ингредиенты, рост среднего чека</p>
            </div>
        </div>

        <v-analytics-filters></v-analytics-filters>
    </div>

    <v-menu-analytics></v-menu-analytics>

    @include('reporting::reporting.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-menu-analytics-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div v-for="i in 6" :key="i" class="shimmer h-48 rounded-xl"></div>
            </div>

            <div v-else class="space-y-6">
                <!-- Top Dishes by Revenue & Quantity -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                                <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Топ блюда по выручке</h3>
                        </div>
                        <div class="space-y-0">
                            <div v-for="(d, i) in (stats.top_by_revenue || []).slice(0,12)" :key="i" class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/30 last:border-0 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold" :class="i < 3 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'text-gray-400'">@{{ i+1 }}</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-white">@{{ d.name }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] text-gray-400">@{{ d.quantity }} шт</span>
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">@{{ money(d.revenue) }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-if="!stats.top_by_revenue?.length" class="text-sm text-gray-400 text-center py-6">Нет данных</p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Топ блюда по количеству</h3>
                        </div>
                        <div class="space-y-0">
                            <div v-for="(d, i) in (stats.top_by_quantity || []).slice(0,12)" :key="i" class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/30 last:border-0 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold" :class="i < 3 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'text-gray-400'">@{{ i+1 }}</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-white">@{{ d.name }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 text-xs font-bold text-emerald-600">@{{ d.quantity }}</span>
                                    <span class="text-[10px] text-gray-400">@{{ money(d.revenue) }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-if="!stats.top_by_quantity?.length" class="text-sm text-gray-400 text-center py-6">Нет данных</p>
                    </div>
                </div>

                <!-- Rates: Attach, Customization, New Dish -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="flex h-9 w-9 mx-auto items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30 mb-3">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </span>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Допродажа</p>
                        <p class="text-3xl font-extrabold text-emerald-600">@{{ fmt(stats.attach_rate?.rate) }}<span class="text-lg">%</span></p>
                        <p class="text-[10px] text-gray-400 mt-1">@{{ stats.attach_rate?.with_attach }} / @{{ stats.attach_rate?.total }}</p>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="flex h-9 w-9 mx-auto items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/30 mb-3">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </span>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Кастомизации</p>
                        <p class="text-3xl font-extrabold text-indigo-600">@{{ fmt(stats.customization_rate?.rate) }}<span class="text-lg">%</span></p>
                        <p class="text-[10px] text-gray-400 mt-1">@{{ stats.customization_rate?.customized }} модифиц.</p>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="flex h-9 w-9 mx-auto items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/30 mb-3">
                            <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </span>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Проба новых</p>
                        <p class="text-3xl font-extrabold text-purple-600">@{{ fmt(stats.new_dish_metrics?.trial_rate) }}<span class="text-lg">%</span></p>
                        <p class="text-[10px] text-gray-400 mt-1">@{{ stats.new_dish_metrics?.new_products }} блюд</p>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="flex h-9 w-9 mx-auto items-center justify-center rounded-xl bg-pink-100 dark:bg-pink-900/30 mb-3">
                            <svg class="h-4 w-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </span>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Повтор новых</p>
                        <p class="text-3xl font-extrabold text-pink-600">@{{ fmt(stats.new_dish_metrics?.repeat_rate) }}<span class="text-lg">%</span></p>
                    </div>
                </div>

                <!-- Ingredients -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Топ добавленные ингредиенты</h3>
                        </div>
                        <div v-for="(ing, i) in stats.top_added || []" :key="i" class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/30 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-gray-400 w-4">@{{ i+1 }}</span>
                                <span class="text-sm text-gray-700 dark:text-gray-200">@{{ ing.name }}</span>
                            </div>
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 rounded-full px-2 py-0.5">+@{{ ing.count }}</span>
                        </div>
                        <p v-if="!stats.top_added?.length" class="text-sm text-gray-400 text-center py-4">Нет данных</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                                <svg class="h-3.5 w-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </span>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Топ удалённые ингредиенты</h3>
                        </div>
                        <div v-for="(ing, i) in stats.top_removed || []" :key="i" class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/30 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-gray-400 w-4">@{{ i+1 }}</span>
                                <span class="text-sm text-gray-700 dark:text-gray-200">@{{ ing.name }}</span>
                            </div>
                            <span class="text-xs font-bold text-red-500 bg-red-50 dark:bg-red-900/20 rounded-full px-2 py-0.5">-@{{ ing.count }}</span>
                        </div>
                        <p v-if="!stats.top_removed?.length" class="text-sm text-gray-400 text-center py-4">Нет данных</p>
                    </div>
                </div>

                <!-- Dead Items -->
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                            <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </span>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Неактивные позиции</h3>
                        <span class="text-[10px] text-gray-400 ml-1">(0 продаж за 14+ дней)</span>
                    </div>
                    <div v-if="stats.dead_items?.length" class="grid grid-cols-2 gap-2 md:grid-cols-4">
                        <div v-for="item in stats.dead_items" :key="item.id" class="rounded-lg border border-dashed border-gray-200 dark:border-gray-600 p-3 bg-gray-50/50 dark:bg-gray-700/30">
                            <p class="text-sm font-semibold text-gray-700 dark:text-white truncate">@{{ item.name }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">SKU: @{{ item.sku }}</p>
                        </div>
                    </div>
                    <div v-else class="flex items-center justify-center py-6 gap-2">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm text-emerald-600 font-medium">Все позиции активны</span>
                    </div>
                </div>

                <!-- AOV Uplift -->
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </span>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Рост среднего чека по позициям</h3>
                    </div>
                    <div class="overflow-x-auto" v-if="stats.aov_uplift?.length">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <th class="text-left pb-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Блюдо</th>
                                    <th class="text-right pb-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">AOV с ним</th>
                                    <th class="text-right pb-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">AOV общий</th>
                                    <th class="text-right pb-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Прирост</th>
                                    <th class="text-right pb-3 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Заказов</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, i) in stats.aov_uplift" :key="i" class="border-b border-gray-50 dark:border-gray-700/30 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="py-2.5 font-medium text-gray-700 dark:text-white">@{{ item.name }}</td>
                                    <td class="py-2.5 text-right text-gray-600 dark:text-gray-300">@{{ money(item.aov_with) }}</td>
                                    <td class="py-2.5 text-right text-gray-400">@{{ money(item.aov_overall) }}</td>
                                    <td class="py-2.5 text-right">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold"
                                              :class="item.uplift > 0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20' : 'bg-red-50 text-red-500 dark:bg-red-900/20'">
                                            @{{ item.uplift > 0 ? '+' : '' }}@{{ money(item.uplift) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right text-gray-400">@{{ item.order_count }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-sm text-gray-400 text-center py-6">Недостаточно данных</p>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-menu-analytics', {
            template: '#v-menu-analytics-template',
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
                    fetch(`{{ route('admin.analytics.menu.stats') }}?${params}`)
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
