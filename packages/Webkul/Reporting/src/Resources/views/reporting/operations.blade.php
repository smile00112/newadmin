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

    @include('reporting::reporting.partials.filters')

    @pushOnce('scripts')
    <script type="text/x-template" id="v-operations-dashboard-template">
        <div>
            <div v-if="isLoading" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div v-for="i in 8" :key="i" class="shimmer h-32 rounded-xl"></div>
            </div>

            <div v-else class="space-y-6">
                <!-- Stage Times -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="flex h-8 w-8 mx-auto items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30 mb-3">
                            <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Заказ → Принят</p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ stats.stage_times?.order_to_accepted?.formatted || '—' }}</p>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="flex h-8 w-8 mx-auto items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30 mb-3">
                            <svg class="h-4 w-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                        </span>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Принят → Готов</p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ stats.stage_times?.accepted_to_ready?.formatted || '—' }}</p>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 text-center">
                        <span class="flex h-8 w-8 mx-auto items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30 mb-3">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Готов → Отдан</p>
                        <p class="text-3xl font-extrabold text-gray-900 dark:text-white">@{{ stats.stage_times?.ready_to_served?.formatted || '—' }}</p>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border border-indigo-100 bg-white p-5 shadow-sm dark:border-indigo-900/40 dark:bg-gray-800 text-center" style="background: linear-gradient(135deg, rgba(99,102,241,0.04), rgba(99,102,241,0.08));">
                        <span class="flex h-8 w-8 mx-auto items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30 mb-3">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <p class="text-[10px] font-semibold text-indigo-400 uppercase tracking-wider mb-1">Итого</p>
                        <p class="text-3xl font-extrabold text-indigo-600">@{{ stats.stage_times?.total_avg?.formatted || '—' }}</p>
                    </div>
                </div>

                <!-- Quality Metrics -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30"><svg class="h-3 w-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Некоррект.</span>
                        </div>
                        <p class="text-2xl font-extrabold text-red-500">@{{ fmt(stats.incorrect_orders?.rate) }}<span class="text-base">%</span></p>
                        <p class="text-[10px] text-gray-400 mt-0.5">@{{ stats.incorrect_orders?.count }} из @{{ stats.incorrect_orders?.total }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30"><svg class="h-3 w-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Отмены</span>
                        </div>
                        <p class="text-2xl font-extrabold text-orange-500">@{{ fmt(stats.cancel_refund?.cancel_rate) }}<span class="text-base">%</span></p>
                        <p class="text-[10px] text-gray-400 mt-0.5">@{{ stats.cancel_refund?.cancelled }} отмен</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30"><svg class="h-3 w-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg></span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Возвраты</span>
                        </div>
                        <p class="text-2xl font-extrabold text-amber-500">@{{ fmt(stats.cancel_refund?.refund_rate) }}<span class="text-base">%</span></p>
                        <p class="text-[10px] text-gray-400 mt-0.5">@{{ stats.cancel_refund?.refunded }} возв.</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/30"><svg class="h-3 w-3 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Задержка</span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white">@{{ stats.handoff_delays?.formatted || '—' }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">max: @{{ stats.handoff_delays?.max_seconds }}с</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30"><svg class="h-3 w-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></span>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Без сбоев</span>
                        </div>
                        <p class="text-2xl font-extrabold text-emerald-600">@{{ fmt(stats.crash_free?.crash_free_rate) }}<span class="text-base">%</span></p>
                    </div>
                </div>

                <!-- Payments -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Успех оплат по методам</h3>
                        </div>
                        <div v-if="stats.payment_rate?.by_method?.length" class="space-y-3">
                            <div v-for="m in stats.payment_rate.by_method" :key="m.method" class="flex items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-700/50 px-3 py-2.5">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">@{{ m.method }}</span>
                                <div class="flex items-center gap-3">
                                    <div class="w-20 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all" :class="m.success_rate >= 95 ? 'bg-emerald-500' : 'bg-amber-500'" :style="{ width: Math.min(m.success_rate, 100) + '%' }"></div>
                                    </div>
                                    <span class="text-xs font-bold w-12 text-right" :class="m.success_rate >= 95 ? 'text-emerald-600' : 'text-amber-600'">@{{ fmt(m.success_rate) }}%</span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400 text-center py-4">Нет данных</p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Причины неудачных оплат</h3>
                        </div>
                        <div v-if="stats.fail_reasons?.length" class="space-y-2">
                            <div v-for="r in stats.fail_reasons" :key="r.fail_reason" class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/30 last:border-0">
                                <span class="text-xs text-gray-600 dark:text-gray-300 truncate mr-3">@{{ r.fail_reason }}</span>
                                <span class="text-xs font-bold text-red-500 bg-red-50 dark:bg-red-900/20 rounded-full px-2 py-0.5">@{{ r.count }}</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400 text-center py-4">Нет данных</p>
                    </div>
                </div>

                <!-- NPS & Complaints -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Индекс лояльности (NPS)</h3>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="relative h-24 w-24">
                                <svg class="h-24 w-24 -rotate-90" viewBox="0 0 36 36">
                                    <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                    <path d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831" fill="none" :stroke="(stats.nps?.nps || 0) >= 0 ? '#10b981' : '#ef4444'" stroke-width="3"
                                          :stroke-dasharray="Math.min(Math.abs(stats.nps?.nps || 0), 100) + ', 100'"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-2xl font-extrabold" :class="(stats.nps?.nps || 0) >= 0 ? 'text-emerald-600' : 'text-red-500'">@{{ stats.nps?.nps || 0 }}</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs text-gray-500">Промоутеры: <span class="font-bold text-gray-700 dark:text-white">@{{ stats.nps?.promoters }}</span></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-gray-400"></span>
                                    <span class="text-xs text-gray-500">Пассивные: <span class="font-bold text-gray-700 dark:text-white">@{{ stats.nps?.passives }}</span></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                                    <span class="text-xs text-gray-500">Детракторы: <span class="font-bold text-gray-700 dark:text-white">@{{ stats.nps?.detractors }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Жалобы и обратная связь</h3>
                        </div>
                        <div class="flex gap-3 mb-4">
                            <div class="flex-1 rounded-lg bg-red-50 dark:bg-red-900/20 p-3 text-center">
                                <p class="text-xl font-extrabold text-red-500">@{{ stats.complaints?.total || 0 }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Всего</p>
                            </div>
                            <div class="flex-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center">
                                <p class="text-xl font-extrabold text-emerald-600">@{{ stats.complaints?.resolved || 0 }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Решено</p>
                            </div>
                            <div class="flex-1 rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3 text-center">
                                <p class="text-xl font-extrabold text-gray-900 dark:text-white">@{{ stats.complaints?.avg_resolution_min || 0 }}<span class="text-sm text-gray-400">мин</span></p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Ср. решение</p>
                            </div>
                        </div>
                        <div v-if="stats.complaints?.top_themes?.length">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Топ темы</p>
                            <div v-for="t in stats.complaints.top_themes.slice(0,5)" :key="t.feedback_theme" class="flex items-center justify-between py-1.5 border-b border-gray-50 dark:border-gray-700/30 last:border-0">
                                <span class="text-xs text-gray-600 dark:text-gray-300">@{{ t.feedback_theme }}</span>
                                <span class="text-xs font-bold text-gray-500 bg-gray-100 dark:bg-gray-700 rounded-full px-2 py-0.5">@{{ t.count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Heatmap -->
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Тепловая карта заказов</h3>
                        <span class="text-[10px] text-gray-400 ml-1">(день недели × час)</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr>
                                    <th class="text-left pb-2 pr-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wider w-10"></th>
                                    <th v-for="h in 24" :key="h" class="text-center pb-2 text-[10px] font-medium text-gray-400 px-0.5 w-6">@{{ h - 1 }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="day in 7" :key="day">
                                    <td class="pr-2 py-0.5 text-[10px] font-semibold text-gray-500 whitespace-nowrap">@{{ dayName(day) }}</td>
                                    <td v-for="h in 24" :key="h" class="px-0.5 py-0.5">
                                        <div class="w-full h-6 rounded transition-colors hover:ring-1 hover:ring-indigo-400" :style="{ backgroundColor: heatColor(heatVal(day, h - 1)) }" :title="heatVal(day, h - 1) + ' заказов'"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Legend -->
                    <div class="flex items-center justify-end mt-3 gap-1">
                        <span class="text-[10px] text-gray-400 mr-1">Мало</span>
                        <div class="h-3 w-4 rounded-sm" style="background: rgba(99,102,241,0.1);"></div>
                        <div class="h-3 w-4 rounded-sm" style="background: rgba(99,102,241,0.3);"></div>
                        <div class="h-3 w-4 rounded-sm" style="background: rgba(99,102,241,0.55);"></div>
                        <div class="h-3 w-4 rounded-sm" style="background: rgba(99,102,241,0.8);"></div>
                        <div class="h-3 w-4 rounded-sm" style="background: rgba(99,102,241,0.95);"></div>
                        <span class="text-[10px] text-gray-400 ml-1">Много</span>
                    </div>
                </div>

                <!-- Kiosk Uptime -->
                <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800" v-if="stats.kiosk_uptime?.length">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Доступность киосков</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div v-for="k in stats.kiosk_uptime" :key="k.kiosk" class="rounded-xl border p-4 text-center"
                             :class="k.uptime_pct >= 99 ? 'border-emerald-100 bg-emerald-50/50 dark:border-emerald-900/30 dark:bg-emerald-900/10' : 'border-red-100 bg-red-50/50 dark:border-red-900/30 dark:bg-red-900/10'">
                            <p class="text-sm font-bold text-gray-700 dark:text-white">@{{ k.kiosk }}</p>
                            <p class="text-[10px] text-gray-400 mb-2">@{{ k.location }}</p>
                            <p class="text-2xl font-extrabold" :class="k.uptime_pct >= 99 ? 'text-emerald-600' : 'text-red-500'">@{{ k.uptime_pct }}%</p>
                            <span class="mt-1 inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full" :class="{'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30': k.status === 'online', 'bg-red-100 text-red-700 dark:bg-red-900/30': k.status === 'offline', 'bg-amber-100 text-amber-700 dark:bg-amber-900/30': k.status === 'degraded'}">@{{ k.status }}</span>
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
