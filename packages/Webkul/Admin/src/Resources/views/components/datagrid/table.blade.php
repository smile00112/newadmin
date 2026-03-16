@props(['isMultiRow' => false])

<v-datagrid-table
    :is-loading="isLoading"
    :is-filter-loading="isFilterLoading"
    :available="available"
    :applied="applied"
    @selectAll="selectAll"
    @sort="sort"
    @actionSuccess="get"
>
    {{ $slot }}
</v-datagrid-table>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-datagrid-table-template"
    >
        <div class="w-full">
            <div class="table-responsive box-shadow relative grid w-full overflow-x-auto rounded-2xl bg-white dark:bg-gray-900">
                <!-- Filter Loading Overlay -->
                <transition
                    enter-active-class="transition-opacity duration-200"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-150"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="isFilterLoading"
                        class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/60 backdrop-blur-[1px] dark:bg-gray-900/60"
                    >
                        <div class="flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 shadow-lg dark:bg-gray-800">
                            <svg class="h-5 w-5 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">@lang('admin::app.components.datagrid.toolbar.filter.title')...</span>
                        </div>
                    </div>
                </transition>

                <slot
                    name="header"
                    :is-loading="isLoading"
                    :is-filter-loading="isFilterLoading"
                    :available="available"
                    :applied="applied"
                    :select-all="selectAll"
                    :sort="sort"
                    :perform-action="performAction"
                >
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.head :isMultiRow="$isMultiRow" />
                    </template>

                    <template v-else>
                        <div
                            class="row grid min-h-[52px] items-center gap-3 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-transparent px-5 py-3 font-semibold text-gray-600 dark:border-gray-800 dark:from-gray-800/50 dark:text-gray-300"
                            :style="`grid-template-columns: repeat(${gridsCount}, minmax(150px, 1fr))`"
                        >
                            <!-- Mass Actions -->
                            <p v-if="available.massActions.length">
                                <label for="mass_action_select_all_records" class="cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="mass_action_select_all_records"
                                        id="mass_action_select_all_records"
                                        class="peer hidden"
                                        :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                        @change="selectAll"
                                    >

                                    <span
                                        class="icon-uncheckbox cursor-pointer rounded-lg text-2xl transition-all duration-200 hover:text-indigo-500"
                                        :class="[
                                            applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checked peer-checked:text-indigo-600' : (
                                                applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-partial peer-checked:text-indigo-600' : ''
                                            ),
                                        ]"
                                    >
                                    </span>
                                </label>
                            </p>

                            <!-- Columns -->
                            <template v-for="column in available.columns">
                                <p
                                    class="flex items-center gap-2 break-words text-sm font-medium"
                                    :class="{'cursor-pointer select-none hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors': column.sortable}"
                                    @click="sort(column)"
                                    v-if="column.visibility"
                                >
                                    @{{ column.label }}

                                    <i
                                        class="align-text-bottom text-sm text-indigo-500"
                                        :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                        v-if="column.index == applied.sort.column"
                                    ></i>
                                </p>
                            </template>

                            <!-- Actions -->
                            <p
                                class="place-self-end text-sm font-medium"
                                v-if="available.actions.length"
                            >
                                @lang('admin::app.components.datagrid.table.actions')
                            </p>
                        </div>
                    </template>
                </slot>

                <slot
                    name="body"
                    :is-loading="isLoading"
                    :is-filter-loading="isFilterLoading"
                    :available="available"
                    :applied="applied"
                    :select-all="selectAll"
                    :sort="sort"
                    :perform-action="performAction"
                >
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body :isMultiRow="$isMultiRow" />
                    </template>

                    <template v-else>
                        <template v-if="available.records.length">
                            <div
                                class="row grid items-center gap-3 border-b border-gray-50 px-5 py-4 text-gray-600 transition-all duration-200 hover:bg-indigo-50/30 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800/30"
                                v-for="record in available.records"
                                :style="`grid-template-columns: repeat(${gridsCount}, minmax(150px, 1fr))`"
                            >
                                <!-- Mass Actions -->
                                <p v-if="available.massActions.length">
                                    <label :for="`mass_action_select_record_${record[available.meta.primary_column]}`" class="cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :name="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                            :value="record[available.meta.primary_column]"
                                            :id="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                            class="peer hidden"
                                            v-model="applied.massActions.indices"
                                        >

                                        <span class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-lg text-2xl transition-all duration-200 hover:text-indigo-500 peer-checked:text-indigo-600">
                                        </span>
                                    </label>
                                </p>

                                <!-- Columns -->
                                <template v-for="column in available.columns">
                                    <p
                                        class="break-words text-sm"
                                        v-html="record[column.index]"
                                        v-if="column.visibility"
                                    >
                                    </p>
                                </template>

                                <!-- Actions -->
                                <p
                                    class="place-self-end flex gap-1"
                                    v-if="available.actions.length"
                                >
                                    <span
                                        class="cursor-pointer rounded-lg p-2 text-xl transition-all duration-200 hover:bg-indigo-100 hover:text-indigo-600 dark:hover:bg-gray-700 dark:hover:text-indigo-400 max-sm:place-self-center"
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
                            <div class="row grid border-b border-gray-50 px-5 py-8 text-center text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-3">
                                    <span class="icon-folder text-5xl text-gray-300 dark:text-gray-600"></span>
                                    <p class="text-sm">
                                        @lang('admin::app.components.datagrid.table.no-records-available')
                                    </p>
                                </div>
                            </div>
                        </template>
                    </template>
                </slot>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-datagrid-table', {
            template: '#v-datagrid-table-template',

            props: ['isLoading', 'isFilterLoading', 'available', 'applied'],

            computed: {
                gridsCount() {
                    let count = this.available.columns.filter((column) => column.visibility).length;

                    if (this.available.actions.length) {
                        ++count;
                    }

                    if (this.available.massActions.length) {
                        ++count;
                    }

                    return count;
                },
            },

            methods: {
                /**
                 * Select all records in the datagrid.
                 *
                 * @returns {void}
                 */
                selectAll() {
                    this.$emit('selectAll');
                },

                /**
                 * Perform a sorting operation on the specified column.
                 *
                 * @param {object} column
                 * @returns {void}
                 */
                sort(column) {
                    this.$emit('sort', column);
                },

                /**
                 * Perform the specified action.
                 *
                 * @param {object} action
                 * @returns {void}
                 */
                performAction(action) {
                    const method = action.method.toLowerCase();

                    switch (method) {
                        case 'get':
                            window.location.href = action.url;

                            break;

                        case 'post':
                        case 'put':
                        case 'patch':
                        case 'delete':
                            this.$emitter.emit('open-confirm-modal', {
                                agree: () => {
                                    this.$axios[method](action.url)
                                        .then(response => {
                                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                            this.$emit('actionSuccess', response.data);
                                        })
                                        .catch((error) => {
                                            this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });

                                            this.$emit('actionError', error.response.data);
                                        });
                                }
                            });

                            break;

                        default:
                            console.error('Method not supported.');

                            break;
                    }
                },
            },
        });
    </script>
@endpushOnce
