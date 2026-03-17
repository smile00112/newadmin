<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.application_errors.index.title')
    </x-slot>

    <v-application-errors
        :initial-stats='@json($stats)'
        src="{{ route('admin.application_errors.index') }}"
    ></v-application-errors>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-application-errors-template"
        >
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex items-center justify-center w-11 h-11 rounded-xl shadow-lg shadow-violet-500/30"
                            style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); min-width:44px;"
                        >
                            <span class="icon-bug text-lg text-white"></span>
                        </div>

                        <div>
                            <p class="text-xl font-bold text-gray-800 dark:text-white">
                                @lang('admin::app.application_errors.index.title')
                            </p>

                            <p class="text-xs text-gray-400">
                                @lang('admin::app.application_errors.index.subtitle')
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stats cards -->
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="box-shadow relative overflow-hidden rounded-2xl bg-white/80 p-4 dark:bg-gray-900">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.application_errors.index.stats.total')
                                </p>

                                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    @{{ stats.total }}
                                </p>
                            </div>

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-100 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-300">
                                <span class="icon-bug text-xl"></span>
                            </div>
                        </div>
                    </div>

                    <div class="box-shadow relative overflow-hidden rounded-2xl bg-white/80 p-4 dark:bg-gray-900">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.application_errors.index.stats.unread')
                                </p>

                                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    @{{ stats.unread }}
                                </p>
                            </div>

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                <span class="icon-email text-xl"></span>
                            </div>
                        </div>
                    </div>

                    <div class="box-shadow relative overflow-hidden rounded-2xl bg-white/80 p-4 dark:bg-gray-900">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.application_errors.index.stats.today')
                                </p>

                                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    @{{ stats.today }}
                                </p>
                            </div>

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300">
                                <span class="icon-flame text-xl"></span>
                            </div>
                        </div>
                    </div>

                    <div class="box-shadow relative overflow-hidden rounded-2xl bg-white/80 p-4 dark:bg-gray-900">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.application_errors.index.stats.critical')
                                </p>

                                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                    @{{ stats.critical }}
                                </p>
                            </div>

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300">
                                <span class="icon-lightning text-xl"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="box-shadow rounded-2xl bg-white/80 p-4 dark:bg-gray-900">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative flex-1 min-w-[180px]">
                            <span class="icon-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base"></span>

                            <input
                                type="text"
                                v-model="filters.q"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-9 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500 dark:focus:border-indigo-400 dark:focus:ring-indigo-500/20"
                                placeholder="@lang('admin::app.application_errors.index.filters.search_placeholder')"
                            />
                        </div>

                        <select
                            v-model="filters.level"
                            class="min-w-[140px] rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-500/20"
                        >
                            <option value="">
                                @lang('admin::app.application_errors.index.filters.all_levels')
                            </option>
                            <option value="error">@lang('admin::app.application_errors.index.level.error')</option>
                            <option value="warning">@lang('admin::app.application_errors.index.level.warning')</option>
                            <option value="critical">@lang('admin::app.application_errors.index.level.critical')</option>
                            <option value="info">@lang('admin::app.application_errors.index.level.info')</option>
                        </select>

                        <select
                            v-model="filters.platform"
                            class="min-w-[140px] rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-500/20"
                        >
                            <option value="">
                                @lang('admin::app.application_errors.index.filters.all_platforms')
                            </option>
                            <option value="ios">iOS</option>
                            <option value="android">Android</option>
                            <option value="web">Web</option>
                        </select>

                        <select
                            v-model="filters.status"
                            class="min-w-[120px] rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-500/20"
                        >
                            <option value="">
                                @lang('admin::app.application_errors.index.filters.all_statuses')
                            </option>
                            <option value="unread">@lang('admin::app.application_errors.index.filters.unread')</option>
                            <option value="read">@lang('admin::app.application_errors.index.filters.read')</option>
                        </select>

                        <button
                            type="button"
                            class="primary-button flex items-center gap-2 px-4 py-2.5 text-sm"
                            @click="applyFilters"
                        >
                            <span class="icon-search text-base"></span>
                        </button>

                        <button
                            type="button"
                            class="transparent-button flex items-center gap-1.5 px-3 py-2.5 text-sm"
                            @click="resetFilters"
                        >
                            <span class="icon-cross text-base"></span>
                        </button>
                    </div>
                </div>

                <!-- Tabs + found + clear -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-1 rounded-full bg-gray-100 p-1 text-xs font-medium text-gray-600 dark:bg-gray-900 dark:text-gray-300">
                        <button
                            type="button"
                            class="rounded-full px-3 py-1 transition"
                            :class="tab === 'all' ? 'bg-white shadow-sm dark:bg-gray-800 text-gray-900 dark:text-white' : 'bg-transparent'"
                            @click="setTab('all')"
                        >
                            @lang('admin::app.application_errors.index.tabs.all')
                            <span class="ml-1 rounded-full bg-gray-200 px-1.5 py-0.5 text-[10px] text-gray-700 dark:bg-gray-800 dark:text-gray-200">@{{ counts.all }}</span>
                        </button>

                        <button
                            type="button"
                            class="rounded-full px-3 py-1 transition"
                            :class="tab === 'manager' ? 'bg-white shadow-sm dark:bg-gray-800 text-amber-700 dark:text-amber-300' : 'bg-transparent'"
                            @click="setTab('manager')"
                        >
                            @lang('admin::app.application_errors.index.tabs.manager')
                            <span class="ml-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] text-amber-700 dark:bg-amber-900/60 dark:text-amber-200">@{{ counts.manager }}</span>
                        </button>

                        <button
                            type="button"
                            class="rounded-full px-3 py-1 transition"
                            :class="tab === 'developer' ? 'bg-white shadow-sm dark:bg-gray-800 text-indigo-700 dark:text-indigo-300' : 'bg-transparent'"
                            @click="setTab('developer')"
                        >
                            @lang('admin::app.application_errors.index.tabs.developer')
                            <span class="ml-1 rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-200">@{{ counts.developer }}</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <p>
                            @lang('admin::app.application_errors.index.table.found')
                            <span class="font-semibold text-gray-900 dark:text-white">@{{ meta.total }}</span>
                        </p>

                        <button
                            type="button"
                            class="danger-button flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs"
                            @click="confirmClearAll"
                            :disabled="!items.length"
                        >
                            <span class="icon-delete text-sm"></span>
                            <span>@lang('admin::app.application_errors.index.table.clear_all')</span>
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="box-shadow overflow-hidden rounded-2xl bg-white/80 dark:bg-gray-900">
                    <template v-if="loading">
                        <div class="p-6 text-sm text-gray-500 dark:text-gray-400">
                            @lang('admin::app.application_errors.index.loading')
                        </div>
                    </template>

                    <template v-else>
                        <template v-if="items.length">
                            <div class="hidden grid-cols-[minmax(0,2.5fr)_minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_auto] border-b border-gray-100 bg-gray-50 px-6 py-3 text-xs font-medium uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-400 sm:grid">
                                <div>@lang('admin::app.application_errors.index.table.error')</div>
                                <div class="text-center">@lang('admin::app.application_errors.index.table.assigned_to')</div>
                                <div class="text-center">@lang('admin::app.application_errors.index.table.level')</div>
                                <div class="text-center">@lang('admin::app.application_errors.index.table.platform')</div>
                                <div class="text-right">@lang('admin::app.application_errors.index.table.time')</div>
                                <div class="w-10 text-right"></div>
                            </div>

                            <div>
                                <div
                                    v-for="item in items"
                                    :key="item.id"
                                    class="cursor-pointer border-b border-gray-100 px-4 py-4 text-sm text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-200 dark:hover:bg-gray-900/70 sm:grid sm:grid-cols-[minmax(0,2.5fr)_minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.2fr)_auto] sm:items-center sm:gap-4"
                                    @click="openDetails(item)"
                                >
                                    <!-- Error message -->
                                    <div class="space-y-1">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            @{{ item.message }}
                                        </p>

                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            @{{ item.code || item.source || '—' }}
                                        </p>
                                    </div>

                                    <!-- Assigned to -->
                                    <div class="mt-3 flex items-center sm:mt-0 sm:justify-center">
                                        <span
                                            v-if="item.assigned_to === 'developer'"
                                            class="inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-200"
                                        >
                                            <span class="icon-code text-sm"></span>
                                            @lang('admin::app.application_errors.index.classification.developer')
                                        </span>

                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/20 dark:text-amber-200"
                                        >
                                            <span class="icon-user text-sm"></span>
                                            @lang('admin::app.application_errors.index.classification.manager')
                                        </span>
                                    </div>

                                    <!-- Level -->
                                    <div class="mt-3 sm:mt-0 sm:text-center">
                                        <span
                                            v-if="item.level === 'critical'"
                                            class="label-canceled"
                                        >
                                            @lang('admin::app.application_errors.index.level.critical')
                                        </span>
                                        <span
                                            v-else-if="item.level === 'warning'"
                                            class="label-pending"
                                        >
                                            @lang('admin::app.application_errors.index.level.warning')
                                        </span>
                                        <span
                                            v-else-if="item.level === 'info'"
                                            class="label-info"
                                        >
                                            @lang('admin::app.application_errors.index.level.info')
                                        </span>
                                        <span
                                            v-else
                                            class="label-closed"
                                        >
                                            @lang('admin::app.application_errors.index.level.error')
                                        </span>
                                    </div>

                                    <!-- Platform -->
                                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 sm:mt-0 sm:justify-center">
                                        <template v-if="item.platform === 'ios'">
                                            <span class="icon-apple text-base"></span>
                                            <span>iOS</span>
                                        </template>
                                        <template v-else-if="item.platform === 'android'">
                                            <span class="icon-android text-base"></span>
                                            <span>Android</span>
                                        </template>
                                        <template v-else-if="item.platform === 'web'">
                                            <span class="icon-store text-base"></span>
                                            <span>Web</span>
                                        </template>
                                        <template v-else>
                                            <span class="icon-info text-base"></span>
                                            <span>—</span>
                                        </template>
                                    </div>

                                    <!-- Time -->
                                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 sm:mt-0 sm:text-right">
                                        @{{ item.created_at }}
                                    </div>

                                    <!-- Actions -->
                                    <div
                                        class="mt-3 flex justify-end sm:mt-0"
                                        @click.stop
                                    >
                                        <button
                                            type="button"
                                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-300"
                                            @click="deleteItem(item)"
                                        >
                                            <span class="icon-delete text-lg"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination simple -->
                            <div
                                v-if="meta.last_page > 1"
                                class="flex items-center justify-between gap-4 border-t border-gray-100 px-4 py-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400"
                            >
                                <p>
                                    @lang('admin::app.application_errors.index.pagination.label', ['from' => '@{{ meta.current_page }}', 'to' => '@{{ meta.last_page }}'])
                                </p>

                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        class="transparent-button rounded-full px-3 py-1"
                                        :disabled="meta.current_page <= 1"
                                        @click="changePage(meta.current_page - 1)"
                                    >
                                        ‹
                                    </button>

                                    <button
                                        type="button"
                                        class="transparent-button rounded-full px-3 py-1"
                                        :disabled="meta.current_page >= meta.last_page"
                                        @click="changePage(meta.current_page + 1)"
                                    >
                                        ›
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    @lang('admin::app.application_errors.index.empty')
                                </p>
                            </div>
                        </template>
                    </template>
                </div>

                <!-- Details modal -->
                <div
                    v-if="showModal && selected"
                    class="fixed inset-0 z-40 flex items-center justify-center bg-black/30 px-4 py-6 backdrop-blur-sm"
                    @click.self="closeModal"
                >
                    <div class="relative max-h-[90vh] w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-950">
                        <!-- Modal header -->
                        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <span class="icon-settings text-xl text-gray-400 dark:text-gray-500"></span>

                                <p class="text-base font-semibold text-gray-900 dark:text-white">
                                    @lang('admin::app.application_errors.show.details')
                                </p>
                            </div>

                            <button
                                type="button"
                                class="rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                @click="closeModal"
                            >
                                <span class="icon-cross text-lg"></span>
                            </button>
                        </div>

                        <!-- Modal body -->
                        <div class="space-y-5 overflow-y-auto px-6 py-5 text-sm text-gray-800 dark:text-gray-100" style="max-height: calc(90vh - 130px);">
                            <!-- Level / platform / date badges -->
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span
                                    v-if="selected.level === 'critical'"
                                    class="label-canceled"
                                >
                                    @lang('admin::app.application_errors.index.level.critical')
                                </span>
                                <span
                                    v-else-if="selected.level === 'warning'"
                                    class="label-pending"
                                >
                                    @lang('admin::app.application_errors.index.level.warning')
                                </span>
                                <span
                                    v-else-if="selected.level === 'info'"
                                    class="label-info"
                                >
                                    @lang('admin::app.application_errors.index.level.info')
                                </span>
                                <span
                                    v-else
                                    class="label-closed"
                                >
                                    @lang('admin::app.application_errors.index.level.error')
                                </span>

                                <span class="rounded-full bg-violet-100 px-2.5 py-1 text-[11px] font-medium text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">
                                    @{{ selected.platform || '—' }}
                                </span>

                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    @{{ selected.created_at }}
                                </span>
                            </div>

                            <!-- Error message -->
                            <div class="space-y-1.5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.application_errors.index.modal.error_label')
                                </p>

                                <pre class="max-h-40 overflow-auto rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-xs leading-relaxed text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 whitespace-pre-wrap break-words">@{{ selected.message }}</pre>
                            </div>

                            <!-- Context (code / source) -->
                            <div v-if="selected.code || selected.source" class="space-y-1.5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.application_errors.index.modal.context')
                                </p>

                                <p class="rounded-xl bg-amber-50 px-4 py-3 text-xs font-mono font-medium text-amber-800 dark:bg-amber-500/10 dark:text-amber-200">
                                    @{{ selected.code || selected.source }}
                                </p>
                            </div>

                            <!-- Stack trace -->
                            <div v-if="selected.trace" class="space-y-1.5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Stack Trace:
                                </p>

                                <pre class="max-h-48 overflow-auto rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-[11px] leading-relaxed text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 whitespace-pre-wrap break-words">@{{ selected.trace }}</pre>
                            </div>

                            <!-- OS / IP / App info -->
                            <div v-if="selected.context_obj && (selected.context_obj.os_version || selected.context_obj.ip || selected.context_obj.app_name)" class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
                                <p v-if="selected.context_obj.os_version">
                                    <span class="font-semibold text-gray-900 dark:text-white">@lang('admin::app.application_errors.show.os'):</span>
                                    @{{ selected.context_obj.os_version }}
                                </p>

                                <p v-if="selected.context_obj.ip">
                                    <span class="font-semibold text-gray-900 dark:text-white">@lang('admin::app.application_errors.show.ip'):</span>
                                    @{{ selected.context_obj.ip }}
                                </p>

                                <p v-if="selected.context_obj.app_name">
                                    <span class="font-semibold text-gray-900 dark:text-white">@lang('admin::app.application_errors.show.app_name'):</span>
                                    @{{ selected.context_obj.app_name }}
                                </p>
                            </div>

                            <!-- File info -->
                            <div v-if="selected.file" class="space-y-1.5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.application_errors.show.file')
                                </p>

                                <p class="break-all rounded-xl bg-gray-50 px-4 py-3 text-xs font-mono text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                                    @{{ selected.file }}<template v-if="selected.line">:@{{ selected.line }}</template>
                                </p>
                            </div>

                            <!-- Classification card -->
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="selected.assigned_to === 'developer'"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200"
                                    >
                                        <span class="icon-code text-sm"></span>
                                        @lang('admin::app.application_errors.index.classification.developer')
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-200"
                                    >
                                        <span class="icon-user text-sm"></span>
                                        @lang('admin::app.application_errors.index.classification.manager')
                                    </span>

                                    <span class="label-canceled text-[11px]">
                                        @lang('admin::app.application_errors.index.classification.should_fix')
                                    </span>

                                    <span
                                        v-if="selected.assigned_to === 'developer'"
                                        class="inline-flex rounded-full bg-blue-500/10 px-2.5 py-1 text-[11px] font-medium text-blue-700 dark:bg-blue-500/20 dark:text-blue-200"
                                    >
                                        @lang('admin::app.application_errors.index.classification.needs_developer')
                                    </span>
                                </div>

                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.application_errors.index.classification.description')
                                </p>

                                <p class="mt-1.5 text-xs text-gray-800 dark:text-gray-100">
                                    <span class="font-semibold">@lang('admin::app.application_errors.index.classification.what_to_do')</span>
                                    @lang('admin::app.application_errors.index.classification.recommendation')
                                </p>
                            </div>
                        </div>

                        <!-- Modal footer -->
                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-3 dark:border-gray-800">
                            <button
                                type="button"
                                class="transparent-button px-4 py-2 text-xs"
                                @click="closeModal"
                            >
                                @lang('admin::app.application_errors.show.back')
                            </button>

                            <button
                                type="button"
                                class="primary-button flex items-center gap-2 px-4 py-2 text-xs"
                                @click="markAsRead(selected)"
                                v-if="!selected.is_read"
                            >
                                <span class="icon-check text-sm"></span>
                                <span>@lang('admin::app.application_errors.index.mark_read')</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-application-errors', {
                template: '#v-application-errors-template',

                props: {
                    initialStats: {
                        type: Object,
                        required: true,
                    },

                    src: {
                        type: String,
                        required: true,
                    },
                },

                data() {
                    return {
                        stats: this.initialStats || {
                            total: 0,
                            unread: 0,
                            today: 0,
                            critical: 0,
                        },

                        filters: {
                            q: '',
                            level: '',
                            platform: '',
                            status: '',
                        },

                        tab: 'all',

                        items: [],
                        meta: {
                            current_page: 1,
                            last_page: 1,
                            per_page: 20,
                            total: 0,
                        },

                        counts: {
                            all: 0,
                            manager: 0,
                            developer: 0,
                        },

                        loading: false,

                        showModal: false,
                        selected: null,
                    };
                },

                mounted() {
                    this.fetchItems();
                },

                methods: {
                    buildQuery(page = 1) {
                        const params = new URLSearchParams();

                        if (this.filters.q) {
                            params.append('q', this.filters.q);
                        }

                        if (this.filters.level) {
                            params.append('level', this.filters.level);
                        }

                        if (this.filters.platform) {
                            params.append('platform', this.filters.platform);
                        }

                        if (this.filters.status) {
                            params.append('is_read', this.filters.status === 'read' ? '1' : '0');
                        }

                        if (this.tab !== 'all') {
                            params.append('assigned_to', this.tab);
                        }

                        params.append('page', page.toString());

                        return params.toString();
                    },

                    async fetchItems(page = 1) {
                        this.loading = true;

                        try {
                            const query = this.buildQuery(page);
                            const response = await fetch(`${this.src}?${query}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            });

                            const data = await response.json();

                            this.items = data.items || [];
                            this.meta = data.meta || this.meta;

                            this.counts.all = this.meta.total || this.items.length;
                            this.counts.manager = this.items.filter((item) => item.assigned_to === 'manager').length;
                            this.counts.developer = this.items.filter((item) => item.assigned_to === 'developer').length;
                        } catch (error) {
                            console.error(error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    applyFilters() {
                        this.changePage(1);
                    },

                    resetFilters() {
                        this.filters.q = '';
                        this.filters.level = '';
                        this.filters.platform = '';
                        this.filters.status = '';
                        this.tab = 'all';

                        this.changePage(1);
                    },

                    setTab(tab) {
                        if (this.tab === tab) {
                            return;
                        }

                        this.tab = tab;
                        this.changePage(1);
                    },

                    changePage(page) {
                        this.meta.current_page = page;
                        this.fetchItems(page);
                    },

                    async openDetails(item) {
                        try {
                            const response = await fetch(`{{ route('admin.application_errors.show', ':id') }}`.replace(':id', item.id), {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            });

                            const data = await response.json();

                            this.selected = {
                                ...data.error,
                                context_obj: data.error.context || null,
                            };

                            this.showModal = true;
                        } catch (error) {
                            console.error(error);
                        }
                    },

                    closeModal() {
                        this.showModal = false;
                        this.selected = null;
                    },

                    async markAsRead(item) {
                        try {
                            await fetch(`{{ route('admin.application_errors.mark_read', ':id') }}`.replace(':id', item.id), {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                            });

                            item.is_read = true;

                            this.stats.unread = Math.max(0, (this.stats.unread || 0) - 1);
                        } catch (error) {
                            console.error(error);
                        }
                    },

                    async deleteItem(item) {
                        if (! confirm('@lang('admin::app.application_errors.index.confirm_delete')')) {
                            return;
                        }

                        try {
                            await fetch(`{{ route('admin.application_errors.destroy', ':id') }}`.replace(':id', item.id), {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                            });

                            this.items = this.items.filter((row) => row.id !== item.id);
                            this.meta.total = Math.max(0, (this.meta.total || 1) - 1);

                            this.stats.total = Math.max(0, (this.stats.total || 1) - 1);
                        } catch (error) {
                            console.error(error);
                        }
                    },

                    async confirmClearAll() {
                        if (! confirm('@lang('admin::app.application_errors.index.confirm_clear_all')')) {
                            return;
                        }

                        try {
                            const query = this.buildQuery(this.meta.current_page);

                            await fetch(`${this.src}?${query}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                            });

                            this.items = [];
                            this.meta.total = 0;

                            this.stats.total = 0;
                            this.stats.unread = 0;
                            this.stats.today = 0;
                            this.stats.critical = 0;
                        } catch (error) {
                            console.error(error);
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
