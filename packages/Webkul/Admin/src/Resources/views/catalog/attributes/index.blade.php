<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.attributes.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <!-- Title -->
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.catalog.attributes.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('catalog.attributes.create'))
                <a href="{{ route('admin.catalog.attributes.create') }}">
                    <div class="primary-button">
                        @lang('admin::app.catalog.attributes.index.create-btn')
                    </div>
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.catalog.attributes.list.before') !!}

    <x-admin::datagrid :src="route('admin.catalog.attributes.index')">
        <!-- Custom Body Slot with Row Click -->
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
                <template v-if="available.records.length">
                    <div
                        class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950 cursor-pointer"
                        v-for="record in available.records"
                        :style="`grid-template-columns: repeat(${(() => {
                            let count = available.columns.filter((column) => column.visibility).length;
                            if (available.actions.length) ++count;
                            if (available.massActions.length) ++count;
                            return count;
                        })()}, minmax(150px, 1fr))`"
                        @click="(() => {
                            const editAction = record.actions.find(action => action.icon && action.icon.includes('icon-edit'));
                            if (editAction && editAction.method === 'GET') {
                                window.location.href = editAction.url;
                            }
                        })()"
                    >
                        <!-- Mass Actions -->
                        <p v-if="available.massActions.length" @click.stop>
                            <label :for="`mass_action_select_record_${record[available.meta.primary_column]}`">
                                <input
                                    type="checkbox"
                                    :name="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                    :value="record[available.meta.primary_column]"
                                    :id="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                    class="peer hidden"
                                    v-model="applied.massActions.indices"
                                >

                                <span class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600">
                                </span>
                            </label>
                        </p>

                        <!-- Columns -->
                        <template v-for="column in available.columns">
                            <p
                                class="break-words"
                                v-html="record[column.index]"
                                v-if="column.visibility"
                            >
                            </p>
                        </template>

                        <!-- Actions -->
                        <p
                            class="place-self-end"
                            v-if="available.actions.length"
                            @click.stop
                        >
                            <span
                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                :class="action.icon"
                                v-text="! action.icon ? action.title : ''"
                                v-for="action in record.actions"
                                @click="performAction(action)"
                            >
                            </span>
                        </p>
                    </div>
                </template>

                <template v-else>
                    <div class="row grid border-b px-4 py-4 text-center text-gray-600 dark:border-gray-800 dark:text-gray-300">
                        <p>
                            @lang('admin::app.components.datagrid.table.no-records-available')
                        </p>
                    </div>
                </template>
            </template>
        </template>
    </x-admin::datagrid>

    {!! view_render_event('bagisto.admin.catalog.attributes.list.after') !!}

</x-admin::layouts>
