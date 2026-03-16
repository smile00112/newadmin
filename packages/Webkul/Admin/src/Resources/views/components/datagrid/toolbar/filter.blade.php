<v-datagrid-filter
    :src="src"
    :is-loading="isLoading"
    :available="available"
    :applied="applied"
    @applyFilters="filter"
    @applySavedFilter="applySavedFilter"
>
    {{ $slot }}
</v-datagrid-filter>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-datagrid-filter-template"
    >
        <!-- Empty slot for right toolbar before -->
        <slot name="right-toolbar-left-before"></slot>

        <slot
            name="filter"
            :available="available"
            :applied="applied"
            :filters="filters"
            :apply-filters="applyFilters"
            :apply-column-values="applyColumnValues"
            :find-applied-column="findAppliedColumn"
            :has-any-applied-column-values="hasAnyAppliedColumnValues"
            :get-applied-column-values="getAppliedColumnValues"
            :remove-applied-column-value="removeAppliedColumnValue"
            :remove-applied-column-all-values="removeAppliedColumnAllValues"
        >
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.toolbar.filter />
            </template>

            <template v-else>
                <x-admin::drawer
                    width="400px"
                    ref="filterDrawer"
                >
                    <x-slot:toggle>
                        <div>
                            <div
                                class="relative inline-flex w-full max-w-max cursor-pointer select-none appearance-none items-center justify-between gap-x-1.5 rounded-lg border bg-white px-3 py-2 text-center text-gray-600 transition-all hover:border-gray-400 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                                :class="{'border-blue-500 bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-500': hasAnyAppliedColumn() }"
                            >
                                <span class="icon-filter text-xl"></span>

                                <span class="text-sm font-medium">
                                    @lang('admin::app.components.datagrid.toolbar.filter.title')
                                </span>

                                <span
                                    class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-bold text-white"
                                    v-if="appliedFilterCount > 0"
                                    v-text="appliedFilterCount"
                                >
                                </span>
                            </div>
                        </div>
                    </x-slot>

                    <x-slot:header>
                        <!-- Apply Filter Title -->
                        <div
                            v-if="! isShowSavedFilters"
                            class="flex items-center justify-between pr-8"
                        >
                            <div class="flex items-center gap-2">
                                <p class="text-lg font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.components.datagrid.filters.title')
                                </p>

                                <span
                                    class="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-blue-600 px-1.5 text-[10px] font-bold text-white"
                                    v-if="appliedFilterCount > 0"
                                    v-text="appliedFilterCount"
                                >
                                </span>

                                <!-- SPA loading spinner -->
                                <svg
                                    v-if="isApplying"
                                    class="h-4 w-4 animate-spin text-blue-600"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <div class="flex items-center gap-3">
                                <div
                                    v-if="hasAnyAppliedColumn()"
                                    class="cursor-pointer rounded-md px-2 py-1 text-xs font-medium text-red-500 transition-all hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                    @click="removeAllAppliedFilters()"
                                >
                                    @lang('admin::app.components.datagrid.filters.custom-filters.clear-all')
                                </div>
                            </div>
                        </div>

                        <!-- Save Filter Title -->
                        <div v-else class="flex items-center gap-x-2 pr-8">
                            <span
                                class="icon-arrow-right rtl:icon-arrow-left mt-0.5 cursor-pointer text-3xl hover:rounded-md hover:bg-gray-100 dark:hover:bg-gray-950"
                                @click="backToFilters"
                            >
                            </span>

                            <p class="text-lg font-semibold text-gray-800 dark:text-white">
                                @{{ applied.savedFilterId ? '@lang('admin::app.components.datagrid.toolbar.filter.update-filter')' : '@lang('admin::app.components.datagrid.toolbar.filter.save-filter')' }}
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content class="!p-0">
                        <template v-if="! isShowSavedFilters">
                            <!-- Quick Filters (Saved Filters) -->
                            <div
                                class="border-b dark:border-gray-800"
                                v-if="savedFilters.available.length > 0"
                            >
                                <div class="px-4 py-3">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        @lang('admin::app.components.datagrid.toolbar.filter.quick-filters')
                                    </p>

                                    <div class="flex flex-wrap gap-2">
                                        <div
                                            v-for="(filter, index) in savedFilters.available"
                                            :key="filter.id"
                                            class="group flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-all hover:border-blue-400 hover:bg-blue-50 dark:hover:border-blue-500 dark:hover:bg-blue-900/20"
                                            :class="applied.savedFilterId == filter.id
                                                ? 'border-blue-500 bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-500'
                                                : 'border-gray-200 text-gray-700 dark:border-gray-700 dark:text-gray-300'"
                                            @click="applySavedFilter(filter)"
                                        >
                                            <span v-text="filter.name"></span>

                                            <span
                                                class="icon-cross inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] text-gray-400 transition-colors hover:bg-red-100 hover:text-red-500 dark:text-gray-500 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                                @click.stop="deleteSavedFilter(filter)"
                                            >
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Filters Tags -->
                            <div
                                class="border-b px-4 py-3 dark:border-gray-800"
                                v-if="filters.columns.length > 0"
                            >
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    @lang('admin::app.components.datagrid.toolbar.filter.selected-filters')
                                </p>

                                <div class="flex flex-wrap gap-1.5">
                                    <template v-for="column in filters.columns">
                                        <!-- Date Tags -->
                                        <template v-if="column.type === 'date' || column.type === 'datetime'">
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                            >
                                                <span class="max-w-[120px] truncate" v-text="column.label"></span>:
                                                <span class="font-normal" v-text="getFormattedDates(column)"></span>
                                                <span
                                                    class="icon-cross cursor-pointer text-sm hover:text-blue-900 dark:hover:text-blue-100"
                                                    @click="removeAppliedColumnValue(column.index)"
                                                ></span>
                                            </span>
                                        </template>

                                        <!-- Multi-value Tags -->
                                        <template v-else-if="column.allow_multiple_values">
                                            <span
                                                v-for="val in column.value"
                                                class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                            >
                                                <span class="max-w-[120px] truncate" v-text="getFilterLabel(column, val)"></span>
                                                <span
                                                    class="icon-cross cursor-pointer text-sm hover:text-blue-900 dark:hover:text-blue-100"
                                                    @click="removeAppliedColumnValue(column.index, val)"
                                                ></span>
                                            </span>
                                        </template>

                                        <!-- Single-value Tags -->
                                        <template v-else>
                                            <span
                                                v-if="column.value !== ''"
                                                class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                            >
                                                <span class="max-w-[120px] truncate" v-text="getFilterLabel(column, column.value)"></span>
                                                <span
                                                    class="icon-cross cursor-pointer text-sm hover:text-blue-900 dark:hover:text-blue-100"
                                                    @click="removeAppliedColumnValue(column.index, column.value)"
                                                ></span>
                                            </span>
                                        </template>
                                    </template>
                                </div>
                            </div>

                            <!-- Filters List -->
                            <div class="flex-1 overflow-y-auto px-4 py-3">
                                <div class="space-y-4">
                                    <template v-for="column in available.columns">
                                        <div v-if="column.filterable" class="filter-group">
                                            <!-- Boolean -->
                                            <template v-if="column.type === 'boolean'">
                                                <!-- Dropdown -->
                                                <template v-if="column.filterable_type === 'dropdown'">
                                                    <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300" v-text="column.label"></label>

                                                    <x-admin::dropdown>
                                                        <x-slot:toggle>
                                                            <button
                                                                type="button"
                                                                class="inline-flex w-full cursor-pointer appearance-none items-center justify-between gap-x-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition-all hover:border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-600"
                                                            >
                                                                <span
                                                                    class="text-gray-400"
                                                                    v-text="'@lang('admin::app.components.datagrid.filters.select')'"
                                                                    v-if="column.allow_multiple_values"
                                                                ></span>

                                                                <span
                                                                    class="text-gray-400"
                                                                    v-text="column.filterable_options.find((option => option.value === getAppliedColumnValues(column.index)))?.label ?? '@lang('admin::app.components.datagrid.filters.select')'"
                                                                    v-else
                                                                ></span>

                                                                <span class="icon-sort-down text-xl"></span>
                                                            </button>
                                                        </x-slot>

                                                        <x-slot:menu class="max-h-[200px] overflow-auto">
                                                            <x-admin::dropdown.menu.item
                                                                v-for="option in column.filterable_options"
                                                                v-text="option.label"
                                                                @click="addFilter(option.value, column)"
                                                            >
                                                            </x-admin::dropdown.menu.item>
                                                        </x-slot>
                                                    </x-admin::dropdown>
                                                </template>

                                                <template v-else></template>
                                            </template>

                                            <!-- Date -->
                                            <template v-else-if="column.type === 'date'">
                                                <!-- Range -->
                                                <template v-if="column.filterable_type === 'date_range'">
                                                    <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300" v-text="column.label"></label>

                                                    <div class="grid grid-cols-2 gap-1.5">
                                                        <p
                                                            class="cursor-pointer rounded-lg border border-gray-200 px-2.5 py-1.5 text-center text-xs font-medium text-gray-600 transition-all hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-blue-500 dark:hover:bg-blue-900/20"
                                                            v-for="option in column.filterable_options"
                                                            v-text="option.label"
                                                            @click="addFilter(
                                                                $event,
                                                                column,
                                                                { quickFilter: { isActive: true, selectedFilter: option } }
                                                            )"
                                                        >
                                                        </p>

                                                        <x-admin::flat-picker.date ::allow-input="false">
                                                            <input
                                                                type="date"
                                                                :name="`${column.index}[from]`"
                                                                value=""
                                                                class="flex min-h-[34px] w-full rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 transition-all hover:border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                                :placeholder="column.label"
                                                                :ref="`${column.index}[from]`"
                                                                @change="addFilter(
                                                                    $event,
                                                                    column,
                                                                    { range: { name: 'from' }, quickFilter: { isActive: false } }
                                                                )"
                                                            />
                                                        </x-admin::flat-picker.date>

                                                        <x-admin::flat-picker.date ::allow-input="false">
                                                            <input
                                                                type="date"
                                                                :name="`${column.index}[to]`"
                                                                value=""
                                                                class="flex min-h-[34px] w-full rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 transition-all hover:border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                                :placeholder="column.label"
                                                                :ref="`${column.index}[from]`"
                                                                @change="addFilter(
                                                                    $event,
                                                                    column,
                                                                    { range: { name: 'to' }, quickFilter: { isActive: false } }
                                                                )"
                                                            />
                                                        </x-admin::flat-picker.date>
                                                    </div>
                                                </template>

                                                <!-- Basic -->
                                                <template v-else>
                                                    <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300" v-text="column.label"></label>

                                                    <x-admin::flat-picker.date ::allow-input="false">
                                                        <input
                                                            type="date"
                                                            :name="column.index"
                                                            value=""
                                                            class="flex min-h-[34px] w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                            :placeholder="column.label"
                                                            :ref="column.index"
                                                            @change="addFilter($event, column)"
                                                        />
                                                    </x-admin::flat-picker.date>
                                                </template>
                                            </template>

                                            <!-- Date Time -->
                                            <template v-else-if="column.type === 'datetime'">
                                                <!-- Range -->
                                                <template v-if="column.filterable_type === 'datetime_range'">
                                                    <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300" v-text="column.label"></label>

                                                    <div class="grid grid-cols-2 gap-1.5">
                                                        <p
                                                            class="cursor-pointer rounded-lg border border-gray-200 px-2.5 py-1.5 text-center text-xs font-medium text-gray-600 transition-all hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600 dark:border-gray-700 dark:text-gray-300 dark:hover:border-blue-500 dark:hover:bg-blue-900/20"
                                                            v-for="option in column.filterable_options"
                                                            v-text="option.label"
                                                            @click="addFilter(
                                                                $event,
                                                                column,
                                                                { quickFilter: { isActive: true, selectedFilter: option } }
                                                            )"
                                                        >
                                                        </p>

                                                        <x-admin::flat-picker.datetime ::allow-input="false">
                                                            <input
                                                                type="datetime-local"
                                                                :name="`${column.index}[from]`"
                                                                value=""
                                                                class="flex min-h-[34px] w-full rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 transition-all hover:border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                                :placeholder="column.label"
                                                                :ref="`${column.index}[from]`"
                                                                @change="addFilter(
                                                                    $event,
                                                                    column,
                                                                    { range: { name: 'from' }, quickFilter: { isActive: false } }
                                                                )"
                                                            />
                                                        </x-admin::flat-picker.datetime>

                                                        <x-admin::flat-picker.datetime ::allow-input="false">
                                                            <input
                                                                type="datetime-local"
                                                                :name="`${column.index}[to]`"
                                                                value=""
                                                                class="flex min-h-[34px] w-full rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 transition-all hover:border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                                :placeholder="column.label"
                                                                :ref="`${column.index}[from]`"
                                                                @change="addFilter(
                                                                    $event,
                                                                    column,
                                                                    { range: { name: 'to' }, quickFilter: { isActive: false } }
                                                                )"
                                                            />
                                                        </x-admin::flat-picker.datetime>
                                                    </div>
                                                </template>

                                                <!-- Basic -->
                                                <template v-else>
                                                    <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300" v-text="column.label"></label>

                                                    <x-admin::flat-picker.datetime ::allow-input="false">
                                                        <input
                                                            type="datetime-local"
                                                            :name="column.index"
                                                            value=""
                                                            class="flex min-h-[34px] w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                            :placeholder="column.label"
                                                            :ref="column.index"
                                                            @change="addFilter($event, column)"
                                                        />
                                                    </x-admin::flat-picker.datetime>
                                                </template>
                                            </template>

                                            <!-- Rest (Text, Integer, etc.) -->
                                            <template v-else>
                                                <!-- Dropdown -->
                                                <template v-if="column.filterable_type === 'dropdown'">
                                                    <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300" v-text="column.label"></label>

                                                    <x-admin::dropdown>
                                                        <x-slot:toggle>
                                                            <button
                                                                type="button"
                                                                class="inline-flex w-full cursor-pointer appearance-none items-center justify-between gap-x-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition-all hover:border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-600"
                                                            >
                                                                <span
                                                                    class="text-gray-400"
                                                                    v-text="'@lang('admin::app.components.datagrid.filters.select')'"
                                                                    v-if="column.allow_multiple_values"
                                                                ></span>

                                                                <span
                                                                    class="text-gray-400"
                                                                    v-text="column.filterable_options.find((option => option.value === getAppliedColumnValues(column.index)))?.label ?? '@lang('admin::app.components.datagrid.filters.select')'"
                                                                    v-else
                                                                ></span>

                                                                <span class="icon-sort-down text-xl"></span>
                                                            </button>
                                                        </x-slot>

                                                        <x-slot:menu class="max-h-[200px] overflow-auto">
                                                            <x-admin::dropdown.menu.item
                                                                v-for="option in column.filterable_options"
                                                                v-text="option.label"
                                                                @click="addFilter(option.value, column)"
                                                            >
                                                            </x-admin::dropdown.menu.item>
                                                        </x-slot>
                                                    </x-admin::dropdown>
                                                </template>

                                                <!-- Basic Text Input -->
                                                <template v-else>
                                                    <label class="mb-1.5 block text-xs font-semibold text-gray-700 dark:text-gray-300" v-text="column.label"></label>

                                                    <input
                                                        type="text"
                                                        class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition-all hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-600"
                                                        :name="column.index"
                                                        :placeholder="column.label"
                                                        @input="debouncedAddFilter($event, column)"
                                                        @keyup.enter.prevent="addFilterFromInput($event, column)"
                                                    />
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Sticky Footer Buttons -->
                            <div class="sticky bottom-0 flex gap-2 border-t bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                                <button
                                    type="button"
                                    v-if="hasAnyColumn"
                                    class="flex-1 rounded-lg border border-gray-200 px-4 py-2 text-center text-sm font-medium text-gray-700 transition-all hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    @click="isShowSavedFilters = ! isShowSavedFilters"
                                    :disabled="! filters.columns.length > 0"
                                >
                                    @{{ applied.savedFilterId ? '@lang('admin::app.components.datagrid.toolbar.filter.update-filter')' : '@lang('admin::app.components.datagrid.toolbar.filter.save-filter')' }}
                                </button>
                            </div>
                        </template>

                        <!-- Save Filter Section -->
                        <template v-else>
                            <div class="flex items-center justify-between px-4 py-4">
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @{{ applied.savedFilterId ? '@lang('admin::app.components.datagrid.toolbar.filter.update-filter')' : '@lang('admin::app.components.datagrid.toolbar.filter.create-new-filter')' }}
                                </p>
                            </div>

                            <div v-if="hasAnyColumn">
                                <!-- Save Filter Form -->
                                <x-admin::form
                                    v-slot="{ meta, errors, handleSubmit }"
                                    as="div"
                                >
                                    <form @submit="handleSubmit($event, createOrUpdateFilter)">
                                        <div class="flex flex-col gap-4">
                                            <!-- Save Filter Name Input Field -->
                                            <div class="flex flex-col gap-2 border-b px-4 dark:border-gray-800">
                                                <x-admin::form.control-group>
                                                    <x-admin::form.control-group.label class="required">
                                                        @lang('admin::app.components.datagrid.toolbar.filter.name')
                                                    </x-admin::form.control-group.label>

                                                    <x-admin::form.control-group.control
                                                        type="hidden"
                                                        name="id"
                                                        ::value="applied.savedFilterId"
                                                    />

                                                    <x-admin::form.control-group.control
                                                        type="text"
                                                        name="name"
                                                        id="name"
                                                        ::value="getAppliedSavedFilter?.name"
                                                        rules="required"
                                                        :label="trans('admin::app.components.datagrid.toolbar.filter.name')"
                                                        :placeholder="trans('admin::app.components.datagrid.toolbar.filter.name')"
                                                    />

                                                    <x-admin::form.control-group.error control-name="name" />
                                                </x-admin::form.control-group>

                                                <!-- Save Filter Form Submit Button -->
                                                <div class="mb-4 flex content-end items-center justify-end">
                                                    <button
                                                        type="submit"
                                                        class="primary-button"
                                                        aria-label="@lang('admin::app.components.datagrid.toolbar.filter.save-btn')"
                                                        :disabled="savedFilters.params.filters.columns.every(column => column.value.length === 0)"
                                                    >
                                                        @{{ applied.savedFilterId ? '@lang('admin::app.components.datagrid.toolbar.filter.update-filter')' : '@lang('admin::app.components.datagrid.toolbar.filter.save-filter')' }}
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="flex flex-col gap-4 px-4">
                                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                    @lang('admin::app.components.datagrid.toolbar.filter.selected-filters')
                                                </p>

                                                <div v-if="! savedFilters.params.filters.columns.every(column => column.value.length === 0)">
                                                    <!-- Applied filters label and value listing for saving custom filter. -->
                                                    <div v-for="column in savedFilters.params.filters.columns">
                                                        <div
                                                            class="flex flex-col gap-2"
                                                            v-if="hasAnyValue(column)"
                                                        >
                                                            <p class="text-xs font-medium text-gray-800 dark:text-white">
                                                                @{{ column.label }}
                                                            </p>

                                                            <div class="mb-4 flex flex-wrap gap-2">
                                                                <!-- Date & Date Time Case -->
                                                                <template v-if="column.type === 'date' || column.type === 'datetime'">
                                                                    <p class="flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                                        <span>
                                                                            @{{ getFormattedDates(column) }}
                                                                        </span>

                                                                        <span
                                                                            class="icon-cross cursor-pointer text-sm ltr:ml-1 rtl:mr-1"
                                                                            @click="removeSavedFilterColumnValue(column, appliedColumnValue)"
                                                                        >
                                                                        </span>
                                                                    </p>
                                                                </template>

                                                                <!-- Rest Case -->
                                                                <template v-else>
                                                                    <!-- If Allow Multiple Values -->
                                                                    <template v-if="column.allow_multiple_values">
                                                                        <p
                                                                            v-for="appliedColumnValue in column.value"
                                                                            class="flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                                                        >
                                                                            <span>
                                                                                @{{ appliedColumnValue }}
                                                                            </span>

                                                                            <span
                                                                                class="icon-cross cursor-pointer text-sm ltr:ml-1 rtl:mr-1"
                                                                                @click="removeSavedFilterColumnValue(column, appliedColumnValue)"
                                                                            >
                                                                            </span>
                                                                        </p>
                                                                    </template>

                                                                    <!-- If Allow Single Value -->
                                                                    <template v-else>
                                                                        <p class="flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                                            <span>
                                                                                @{{ column.value }}
                                                                            </span>

                                                                            <span
                                                                                class="icon-cross cursor-pointer text-sm ltr:ml-1 rtl:mr-1"
                                                                                @click="removeSavedFilterColumnValue(column, column.value)"
                                                                            >
                                                                            </span>
                                                                        </p>
                                                                    </template>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Save Filter Empty Value Placeholder -->
                                                <div v-else>
                                                    <div class="mb-4 flex content-end items-center justify-end">
                                                        <div class="grid">
                                                            <div class="flex items-center gap-5 py-2.5">
                                                                <img
                                                                    src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                                                                    class="h-20 w-20 dark:border-gray-800 dark:mix-blend-exclusion dark:invert"
                                                                >

                                                                <div class="flex flex-col gap-1.5">
                                                                    <p class="text-base font-semibold text-gray-400">
                                                                        @lang('admin::app.components.datagrid.toolbar.filter.empty-title')
                                                                    </p>

                                                                    <p class="text-gray-400">
                                                                        @lang('admin::app.components.datagrid.toolbar.filter.empty-description')
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </x-admin::form>
                            </div>
                        </template>
                    </x-slot>
                </x-admin::drawer>
            </template>
        </slot>
    </script>

    <script type="module">
        app.component('v-datagrid-filter', {
            template: '#v-datagrid-filter-template',

            props: ['isLoading', 'available', 'applied', 'src'],

            emits: ['applyFilters', 'applySavedFilter'],

            data() {
                return {
                    savedFilters: {
                        available: [],

                        applied: null,

                        params: {
                            filters: {
                                columns: [],
                            },
                        },
                    },

                    filters: {
                        columns: [],
                    },

                    isShowSavedFilters: false,

                    isFilterDirty: false,

                    debounceTimer: null,

                    isApplying: false,
                };
            },

            mounted() {
                this.filters.columns = this.getAppliedColumns();

                this.savedFilters.params.filters.columns = JSON.parse(JSON.stringify(this.filters.columns));

                this.getSavedFilters();
            },

            computed: {
                getAppliedSavedFilter() {
                    return this.savedFilters.available.find((filter) => filter.id == this.applied.savedFilterId);
                },

                appliedFilterCount() {
                    return this.filters.columns.reduce((count, column) => {
                        if (column.allow_multiple_values) {
                            return count + (column.value?.length || 0);
                        }
                        return count + (column.value !== '' && column.value !== undefined ? 1 : 0);
                    }, 0);
                },
            },

            methods: {
                /**
                 * Has any column.
                 */
                hasAnyColumn() {
                    return filters.columns.length;
                },

                /**
                 * Get applied columns.
                 */
                getAppliedColumns() {
                    return this.applied.filters.columns.filter((column) => column.index !== 'all');
                },

                /**
                 * Has any applied column.
                 */
                hasAnyAppliedColumn() {
                    return this.getAppliedColumns().length > 0;
                },

                /**
                 * Go back to filters.
                 */
                backToFilters() {
                    this.savedFilters.params.filters.columns = JSON.parse(JSON.stringify(this.filters.columns));

                    this.isShowSavedFilters = ! this.isShowSavedFilters;
                },

                /**
                 * Applies the saved filter.
                 */
                applySavedFilter(filter) {
                    this.$emit('applySavedFilter', filter);
                },

                /**
                 * Remove all applied filters.
                 */
                removeAllAppliedFilters() {
                    this.filters = {
                        columns: [],
                    };

                    this.isFilterDirty = false;

                    this.autoApplyFilters();
                },

                /**
                 * Remove filter option from save filters screen.
                 */
                removeSavedFilterColumnValue(column, value) {
                    if (column.allow_multiple_values) {
                        column.value = column.value.filter((columnValue) => columnValue !== value);
                    } else {
                        column.value = '';
                    }
                },

                /**
                 * Save filters to the database.
                 */
                createOrUpdateFilter(params, { setErrors }) {
                    let applied = JSON.parse(JSON.stringify(this.applied));

                    applied.filters.columns = this.savedFilters.params.filters.columns.filter((column) => this.hasAnyValue(column));

                    if (params.id) {
                        params._method = 'PUT';
                    }

                    this.$axios.post(params.id ? `{{ route('admin.datagrid.saved_filters.update', '') }}/${params.id}` : "{{ route('admin.datagrid.saved_filters.store') }}", {
                        src: this.src,
                        applied,
                        ...params,
                    })
                        .then(response => {
                            if (! params.id) {
                                this.savedFilters.available.push(response.data.data);
                            } else {
                                this.savedFilters.available = this.savedFilters.available.map((filter) => {
                                    if (filter.id == response.data.data.id) {
                                        return response.data.data;
                                    }

                                    return filter;
                                });
                            }

                            this.savedFilters.name = '';

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.isShowSavedFilters = false;
                        })
                        .catch(error => {
                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            } else {
                                this.$emitter.emit('add-flash', { type: 'error',  message: error.response.data.message });
                            }
                        });
                },

                /**
                 * Retrieves the saved filters.
                 */
                getSavedFilters() {
                    this.$axios
                        .get('{{ route('admin.datagrid.saved_filters.index') }}', {
                            params: { src: this.src }
                        })
                        .then(response => {
                            this.savedFilters.available = response.data.data;
                        })
                        .catch(error => {});
                },

                /**
                 * Delete the saved filter.
                 */
                deleteSavedFilter(filter) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            this.$axios.delete(`{{ route('admin.datagrid.saved_filters.destroy', '') }}/${filter.id}`)
                                .then(response => {
                                    this.applySavedFilter(null);

                                    this.savedFilters.available = this.savedFilters.available.filter((savedFilter) => savedFilter.id !== filter.id);

                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                })
                                .catch(error => {
                                    this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                                });
                        }
                    });
                },

                /**
                 * Auto-apply filters immediately (SPA behavior).
                 */
                autoApplyFilters() {
                    this.savedFilters.params.filters.columns = JSON.parse(JSON.stringify(this.filters.columns));

                    this.isApplying = true;

                    this.$emit('applyFilters', this.filters);

                    setTimeout(() => {
                        this.isApplying = false;
                    }, 600);
                },

                /**
                 * Apply all added filters (manual trigger, also closes drawer).
                 */
                applyFilters() {
                    this.savedFilters.params.filters.columns = JSON.parse(JSON.stringify(this.filters.columns));

                    this.$emit('applyFilters', this.filters);

                    this.isFilterDirty = false;

                    this.$refs.filterDrawer.close();
                },

                /**
                 * Debounced text input filter - waits 500ms after typing stops.
                 * Captures value immediately to avoid stale event references.
                 */
                debouncedAddFilter($event, column) {
                    if (this.debounceTimer) {
                        clearTimeout(this.debounceTimer);
                    }

                    const capturedValue = $event.target.value;

                    this.debounceTimer = setTimeout(() => {
                        if (capturedValue.trim() === '') return;

                        this.addFilter(capturedValue, column);
                    }, 500);
                },

                /**
                 * Immediate filter from Enter key - cancels any pending debounce.
                 */
                addFilterFromInput($event, column) {
                    if (this.debounceTimer) {
                        clearTimeout(this.debounceTimer);
                        this.debounceTimer = null;
                    }

                    const value = $event.target.value;
                    if (value.trim() === '') return;

                    this.addFilter(value, column);
                },

                /**
                 * Get human-readable label for a filter value.
                 */
                getFilterLabel(column, value) {
                    const availableColumn = this.available.columns.find(c => c.index === column.index);

                    if (availableColumn?.filterable_options?.length) {
                        const option = availableColumn.filterable_options.find(o => o.value == value);
                        if (option) return option.label;
                    }

                    return value;
                },

                /**
                 * Add filter.
                 */
                addFilter($event, column = null, additional = {}) {
                    let quickFilter = additional?.quickFilter;

                    if (quickFilter?.isActive) {
                        let options = quickFilter.selectedFilter;

                        switch (column.type) {
                            case 'date':
                            case 'datetime':
                                this.applyColumnValues(column, options.name);

                                break;

                            default:
                                break;
                        }
                    } else {
                        let value;

                        if (typeof $event === 'string') {
                            value = $event;
                        } else if ($event?.target?.value !== undefined) {
                            value = $event.target.value;
                        } else {
                            value = $event;
                        }

                        this.applyColumnValues(column, value, additional);
                    }
                },

                /**
                 * Apply column values.
                 */
                applyColumnValues(column, requestedValue, additional = {}) {
                    let appliedColumn = this.findAppliedColumn(column?.index);

                    if (
                        requestedValue === undefined ||
                        requestedValue === '' ||
                        (appliedColumn?.allow_multiple_values && appliedColumn?.value.includes(requestedValue)) ||
                        (! appliedColumn?.allow_multiple_values && appliedColumn?.value === requestedValue)
                    ) {
                        return;
                    }

                    switch (column.type) {
                        case 'date':
                        case 'datetime':
                            let { range } = additional;

                            if (appliedColumn) {
                                if (range) {
                                    let appliedRanges = ['', ''];

                                    if (typeof appliedColumn.value !== 'string') {
                                        appliedRanges = appliedColumn.value[0];
                                    }

                                    if (range.name == 'from') {
                                        appliedRanges[0] = requestedValue;
                                    }

                                    if (range.name == 'to') {
                                        appliedRanges[1] = requestedValue;
                                    }

                                    appliedColumn.value = [appliedRanges];
                                } else {
                                    appliedColumn.value = requestedValue;
                                }
                            } else {
                                if (range) {
                                    let appliedRanges = ['', ''];

                                    if (range.name == 'from') {
                                        appliedRanges[0] = requestedValue;
                                    }

                                    if (range.name == 'to') {
                                        appliedRanges[1] = requestedValue;
                                    }

                                    this.filters.columns.push({
                                        index: column.index,
                                        label: column.label,
                                        type: column.type,
                                        value: [appliedRanges]
                                    });
                                } else {
                                    this.filters.columns.push({
                                        index: column.index,
                                        label: column.label,
                                        type: column.type,
                                        value: requestedValue
                                    });
                                }
                            }

                            break;

                        default:
                            if (appliedColumn) {
                                if (appliedColumn.allow_multiple_values) {
                                    appliedColumn.value.push(requestedValue);
                                } else {
                                    appliedColumn.value = requestedValue;
                                }
                            } else {
                                this.filters.columns.push({
                                    index: column.index,
                                    label: column.label,
                                    type: column.type,
                                    value: column.allow_multiple_values ? [requestedValue] : requestedValue,
                                    allow_multiple_values: column.allow_multiple_values,
                                });
                            }

                            break;
                    }

                    this.isFilterDirty = true;

                    this.autoApplyFilters();
                },

                /**
                 * Get formatted dates.
                 */
                getFormattedDates(appliedColumn)
                {
                    if (! appliedColumn) {
                        return '';
                    }

                    if (typeof appliedColumn.value === 'string') {
                        const availableColumn = this.available.columns.find(column => column.index === appliedColumn.index);

                        if (availableColumn.filterable_type === 'date_range' || availableColumn.filterable_type === 'datetime_range') {
                            const option = availableColumn.filterable_options.find(option => option.name === appliedColumn.value);

                            return option.label;
                        }

                        return appliedColumn.value;
                    }

                    if (! appliedColumn.value.length) {
                        return '';
                    }

                    return appliedColumn.value[0].join(' to ');
                },

                /**
                 * Check if any values are applied for the specified column.
                 */
                hasAnyValue(column) {
                    if (column.allow_multiple_values) {
                        return column.value.length > 0;
                    }

                    return column.value !== '';
                },

                /**
                 * Find applied column.
                 */
                findAppliedColumn(columnIndex) {
                    return this.filters.columns.find(column => column.index === columnIndex);
                },

                /**
                 * Check if any values are applied for the specified column.
                 */
                hasAnyAppliedColumnValues(columnIndex) {
                    let appliedColumn = this.findAppliedColumn(columnIndex);

                    if (! appliedColumn) {
                        return false;
                    }

                    return this.hasAnyValue(appliedColumn);
                },

                /**
                 * Get applied values for the specified column.
                 */
                getAppliedColumnValues(columnIndex) {
                    const appliedColumn = this.findAppliedColumn(columnIndex);

                    if (appliedColumn?.allow_multiple_values) {
                        return appliedColumn?.value ?? [];
                    }

                    return appliedColumn?.value ?? '';
                },

                /**
                 * Remove a specific value from the applied values of the specified column.
                 */
                removeAppliedColumnValue(columnIndex, appliedColumnValue) {
                    let appliedColumn = this.findAppliedColumn(columnIndex);

                    if (appliedColumn?.type === 'date' || appliedColumn?.type === 'datetime') {
                        appliedColumn.value = [];
                    } else {
                        if (appliedColumn.allow_multiple_values) {
                            appliedColumn.value = appliedColumn?.value.filter(value => value !== appliedColumnValue);
                        } else {
                            appliedColumn.value = '';
                        }
                    }

                    if (! appliedColumn.value.length) {
                        this.filters.columns = this.filters.columns.filter(column => column.index !== columnIndex);
                    }

                    this.isFilterDirty = true;

                    this.autoApplyFilters();
                },

                /**
                 * Remove all values from the applied values of the specified column.
                 */
                removeAppliedColumnAllValues(columnIndex) {
                    this.filters.columns = this.filters.columns.filter(column => column.index !== columnIndex);

                    this.isFilterDirty = true;

                    this.autoApplyFilters();
                },
            },
        });
    </script>
@endpushOnce
