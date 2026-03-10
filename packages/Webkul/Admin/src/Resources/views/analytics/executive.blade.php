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

    @include('admin::analytics.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-analytics-filters-template">
        <div class="flex gap-2 items-center">
            <input type="date" v-model="startDate" class="rounded-md border px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
            <input type="date" v-model="endDate" class="rounded-md border px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" />
            <select v-model="channel" class="rounded-md border px-3 py-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                <option value="">Все каналы</option>
                <option value="app">App</option>
                <option value="kiosk">Kiosk</option>
                <option value="cashier">Cashier</option>
            </select>
            <button @click="$emitter.emit('analytics-filter-changed', { start: startDate, end: endDate, channel: channel })"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700 transition">
                Применить
            </button>
        </div>
    </script>

    <script type="text/x-template" id="v-executive-dashboard-template">
        <div>
            <!-- Loading state -->
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div v-for="i in 8" :key="i" class="shimmer h-28 rounded-lg"></div>
            </div>

            <div v-else>
                <!-- North Star KPI Cards -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
                    <!-- Online Order Share -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 uppercase">Доля онлайн-заказов</span>
                            <span :class="stats.north_star?.online_order_share?.on_track ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="text-xs px-2 py-0.5 rounded-full font-medium">
                                Цель: 90%
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">@{{ formatPct(stats.north_star?.online_order_share?.value) }}%</p>
                        <p class="text-xs text-gray-400 mt-1">@{{ stats.north_star?.online_order_share?.online }} / @{{ stats.north_star?.online_order_share?.total }} заказов</p>
                    </div>

                    <!-- GMV -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 uppercase">Общая выручка (GMV)</span>
                            <span :class="stats.north_star?.gmv?.change >= 0 ? 'text-green-600' : 'text-red-600'" class="text-xs font-medium">
                                @{{ stats.north_star?.gmv?.change >= 0 ? '+' : '' }}@{{ stats.north_star?.gmv?.change }}%
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">@{{ formatMoney(stats.north_star?.gmv?.value) }}</p>
                        <p class="text-xs text-gray-400 mt-1">Пред. период: @{{ formatMoney(stats.north_star?.gmv?.previous) }}</p>
                    </div>

                    <!-- AOV -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 uppercase">AOV (Средний чек)</span>
                            <span :class="stats.north_star?.aov?.change >= 0 ? 'text-green-600' : 'text-red-600'" class="text-xs font-medium">
                                @{{ stats.north_star?.aov?.change >= 0 ? '+' : '' }}@{{ stats.north_star?.aov?.change }}%
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">@{{ formatMoney(stats.north_star?.aov?.value) }}</p>
                    </div>

                    <!-- Avg Order→Ready -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-xs font-medium text-gray-500 uppercase">Среднее время заказ→готов</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-2">@{{ stats.north_star?.avg_order_ready?.formatted || '—' }}</p>
                    </div>

                    <!-- SLA -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-xs font-medium text-gray-500 uppercase">Заказы в рамках SLA</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-2">@{{ formatPct(stats.north_star?.orders_within_sla?.value) }}%</p>
                        <p class="text-xs text-gray-400 mt-1">@{{ stats.north_star?.orders_within_sla?.within }} / @{{ stats.north_star?.orders_within_sla?.total }}</p>
                    </div>

                    <!-- Repeat Rate -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-xs font-medium text-gray-500 uppercase">Доля повторных клиентов</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-2">@{{ formatPct(stats.north_star?.repeat_rate?.value) }}%</p>
                        <p class="text-xs text-gray-400 mt-1">@{{ stats.north_star?.repeat_rate?.repeat_customers }} повторных клиентов</p>
                    </div>

                    <!-- Payment Success Rate -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-xs font-medium text-gray-500 uppercase">Успешность оплат</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-2">@{{ formatPct(stats.payment_rate?.overall_rate) }}%</p>
                    </div>

                    <!-- Total Orders -->
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-xs font-medium text-gray-500 uppercase">Всего заказов</span>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-2">@{{ stats.north_star?.total_orders }}</p>
                    </div>
                </div>

                <!-- Second Row: Active Users & Revenue Per User -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-xs font-medium text-gray-500 uppercase">Активные пользователи</span>
                        <div class="mt-3 flex gap-6">
                            <div><p class="text-lg font-bold dark:text-white">@{{ stats.active_users?.dau }}</p><p class="text-xs text-gray-400">DAU</p></div>
                            <div><p class="text-lg font-bold dark:text-white">@{{ stats.active_users?.wau }}</p><p class="text-xs text-gray-400">WAU</p></div>
                            <div><p class="text-lg font-bold dark:text-white">@{{ stats.active_users?.mau }}</p><p class="text-xs text-gray-400">MAU</p></div>
                        </div>
                    </div>

                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-xs font-medium text-gray-500 uppercase">Выручка на пользователя</span>
                        <div class="mt-3 flex gap-6">
                            <div><p class="text-lg font-bold dark:text-white">@{{ formatMoney(stats.revenue_per_user?.arpu) }}</p><p class="text-xs text-gray-400">ARPU</p></div>
                            <div><p class="text-lg font-bold dark:text-white">@{{ formatMoney(stats.revenue_per_user?.rppu) }}</p><p class="text-xs text-gray-400">RPPU</p></div>
                        </div>
                    </div>

                    <div class="rounded-lg border p-4 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-xs font-medium text-gray-500 uppercase">Каналы — Выручка</span>
                        <div class="mt-3 space-y-2">
                            <div v-for="(ch, name) in stats.channel_split" :key="name" class="flex justify-between text-sm">
                                <span class="dark:text-gray-300">@{{ ch.channel_name }}</span>
                                <span class="font-medium dark:text-white">@{{ formatMoney(ch.revenue) }} (@{{ ch.orders }} зак.)</span>
                            </div>
                            <div v-if="!stats.channel_split?.length" class="text-sm text-gray-400">Нет данных</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-analytics-filters', {
            template: '#v-analytics-filters-template',
            data() {
                return {
                    startDate: new Date(Date.now() - 30*24*60*60*1000).toISOString().split('T')[0],
                    endDate: new Date().toISOString().split('T')[0],
                    channel: '',
                };
            },
        });

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
