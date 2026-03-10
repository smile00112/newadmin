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

    @include('admin::analytics.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-menu-analytics-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div v-for="i in 6" :key="i" class="shimmer h-48 rounded-lg"></div>
            </div>

            <div v-else class="space-y-6">
                <!-- Top Dishes by Revenue & Quantity -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Топ блюда по выручке</h3>
                        <table class="w-full text-sm">
                            <thead><tr class="text-xs text-gray-400 border-b dark:border-gray-700">
                                <th class="text-left pb-2">#</th><th class="text-left pb-2">Блюдо</th><th class="text-right pb-2">Выручка</th><th class="text-right pb-2">Кол-во</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="(d, i) in (stats.top_by_revenue || []).slice(0,15)" :key="i" class="border-b dark:border-gray-700/50">
                                    <td class="py-1.5 text-gray-400 text-xs">@{{ i+1 }}</td>
                                    <td class="py-1.5 dark:text-white">@{{ d.name }}</td>
                                    <td class="py-1.5 text-right font-medium dark:text-white">@{{ money(d.revenue) }}</td>
                                    <td class="py-1.5 text-right text-gray-500">@{{ d.quantity }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Топ блюда по количеству</h3>
                        <table class="w-full text-sm">
                            <thead><tr class="text-xs text-gray-400 border-b dark:border-gray-700">
                                <th class="text-left pb-2">#</th><th class="text-left pb-2">Блюдо</th><th class="text-right pb-2">Кол-во</th><th class="text-right pb-2">Выручка</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="(d, i) in (stats.top_by_quantity || []).slice(0,15)" :key="i" class="border-b dark:border-gray-700/50">
                                    <td class="py-1.5 text-gray-400 text-xs">@{{ i+1 }}</td>
                                    <td class="py-1.5 dark:text-white">@{{ d.name }}</td>
                                    <td class="py-1.5 text-right font-medium dark:text-white">@{{ d.quantity }}</td>
                                    <td class="py-1.5 text-right text-gray-500">@{{ money(d.revenue) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rates: Attach, Customization, New Dish -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Допродажа (напитки/десерты)</span>
                        <p class="text-2xl font-bold text-emerald-600 mt-2">@{{ fmt(stats.attach_rate?.rate) }}%</p>
                        <p class="text-xs text-gray-400">@{{ stats.attach_rate?.with_attach }} / @{{ stats.attach_rate?.total }}</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Доля кастомизаций</span>
                        <p class="text-2xl font-bold text-indigo-600 mt-2">@{{ fmt(stats.customization_rate?.rate) }}%</p>
                        <p class="text-xs text-gray-400">@{{ stats.customization_rate?.customized }} с модификациями</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Проба новых блюд</span>
                        <p class="text-2xl font-bold text-purple-600 mt-2">@{{ fmt(stats.new_dish_metrics?.trial_rate) }}%</p>
                        <p class="text-xs text-gray-400">@{{ stats.new_dish_metrics?.new_products }} новых блюд</p>
                    </div>
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="text-[11px] text-gray-400 uppercase">Повтор новых блюд</span>
                        <p class="text-2xl font-bold text-purple-600 mt-2">@{{ fmt(stats.new_dish_metrics?.repeat_rate) }}%</p>
                    </div>
                </div>

                <!-- Ingredients -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Топ добавленные ингредиенты</h3>
                        <div v-for="(ing, i) in stats.top_added || []" :key="i" class="flex justify-between text-sm py-1 border-b dark:border-gray-700/50">
                            <span class="dark:text-gray-300">@{{ ing.name }}</span>
                            <span class="font-medium dark:text-white">@{{ ing.count }}</span>
                        </div>
                        <p v-if="!stats.top_added?.length" class="text-sm text-gray-400">Нет данных</p>
                    </div>
                    <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Топ удалённые ингредиенты</h3>
                        <div v-for="(ing, i) in stats.top_removed || []" :key="i" class="flex justify-between text-sm py-1 border-b dark:border-gray-700/50">
                            <span class="dark:text-gray-300">@{{ ing.name }}</span>
                            <span class="font-medium text-red-400">@{{ ing.count }}</span>
                        </div>
                        <p v-if="!stats.top_removed?.length" class="text-sm text-gray-400">Нет данных</p>
                    </div>
                </div>

                <!-- Dead Items -->
                <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Неактивные позиции (0 продаж за 14+ дней)</h3>
                    <div v-if="stats.dead_items?.length" class="grid grid-cols-2 gap-2 md:grid-cols-4">
                        <div v-for="item in stats.dead_items" :key="item.id" class="border rounded p-2 dark:border-gray-600">
                            <p class="text-sm font-medium dark:text-white truncate">@{{ item.name }}</p>
                            <p class="text-[10px] text-gray-400">SKU: @{{ item.sku }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-green-600">Нет «мёртвых» позиций</p>
                </div>

                <!-- AOV Uplift -->
                <div class="rounded-lg border p-5 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">Рост среднего чека по позициям</h3>
                    <table class="w-full text-sm" v-if="stats.aov_uplift?.length">
                        <thead><tr class="text-xs text-gray-400 border-b dark:border-gray-700">
                            <th class="text-left pb-2">Блюдо</th>
                            <th class="text-right pb-2">AOV с ним</th>
                            <th class="text-right pb-2">AOV общий</th>
                            <th class="text-right pb-2">Прирост</th>
                            <th class="text-right pb-2">Заказов</th>
                        </tr></thead>
                        <tbody>
                            <tr v-for="(item, i) in stats.aov_uplift" :key="i" class="border-b dark:border-gray-700/50">
                                <td class="py-1.5 dark:text-white">@{{ item.name }}</td>
                                <td class="py-1.5 text-right dark:text-gray-300">@{{ money(item.aov_with) }}</td>
                                <td class="py-1.5 text-right text-gray-500">@{{ money(item.aov_overall) }}</td>
                                <td class="py-1.5 text-right font-medium" :class="item.uplift > 0 ? 'text-green-600' : 'text-red-500'">
                                    @{{ item.uplift > 0 ? '+' : '' }}@{{ money(item.uplift) }}
                                </td>
                                <td class="py-1.5 text-right text-gray-500">@{{ item.order_count }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-sm text-gray-400">Недостаточно данных</p>
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
