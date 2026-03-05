<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.application_errors.index.title')
    </x-slot>

    <v-application-errors>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 15px rgba(239,68,68,0.3); min-width:44px;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.application_errors.index.title')
                    </p>
                    <p class="text-xs text-gray-400">Ошибки приложения</p>
                </div>
            </div>
        </div>

        <x-admin::shimmer.datagrid />
    </v-application-errors>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-application-errors-template"
        >
            <div>
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 15px rgba(239,68,68,0.3); min-width:44px;">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-800 dark:text-white">
                                @lang('admin::app.application_errors.index.title')
                            </p>
                            <p class="text-xs text-gray-400">Ошибки приложения</p>
                        </div>
                    </div>
                </div>

                <x-admin::datagrid
                    :src="route('admin.application_errors.index')"
                    ref="datagrid"
                >
                    <template #body="{
                        isLoading,
                        available,
                        applied,
                        selectAll,
                        sort,
                        performAction
                    }">
                        <template v-if="isLoading">
                            <x-admin::shimmer.datagrid.table.body />
                        </template>

                        <template v-else>
                            <template v-if="available.records && available.records.length">
                                <div
                                    v-for="record in available.records"
                                    class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                                    :style="`grid-template-columns: repeat(${(available.columns?.length || 5) + (available.actions?.length ? 1 : 0)}, minmax(0, 1fr))`"
                                >
                                    <p>@{{ record.error_id }}</p>
                                    <p class="break-words text-sm">@{{ record.message }}</p>
                                    <p>@{{ record.code || '—' }}</p>
                                    <p>@{{ record.source || '—' }}</p>
                                    <p>@{{ record.created_at }}</p>
                                    <div v-if="record.actions && record.actions.length" class="flex justify-end">
                                        <a :href="record.actions.find(a => a.index === 'view')?.url">
                                            <span
                                                :class="record.actions.find(a => a.index === 'view')?.icon"
                                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                            </span>
                                        </a>
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
                    </template>
                </x-admin::datagrid>
            </div>
        </script>

        <script type="module">
            app.component('v-application-errors', {
                template: '#v-application-errors-template',
            });
        </script>
    @endPushOnce
</x-admin::layouts>
