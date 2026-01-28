@php
    $bonusLevelRepository = app(\Webkul\Bonus\Repositories\BonusLevelRepository::class);
    $levels = $bonusLevelRepository->all()->toArray();
@endphp

<div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
        @lang('bonus::app.admin.settings.levels.title')
    </p>

    <v-bonus-levels :initial-levels="{{ json_encode($levels) }}"></v-bonus-levels>
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-bonus-levels-template"
    >
        <div class="grid gap-4">
            <!-- Header with Create Button -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    @lang('bonus::app.admin.settings.levels.info')
                </p>
                <a
                    href="{{ route('admin.bonus.levels.create') }}"
                    class="primary-button"
                >
                    @lang('bonus::app.admin.levels.create')
                </a>
            </div>

            <!-- Loading State -->
            <div
                v-if="isLoading"
                class="flex items-center justify-center py-10"
            >
                <img
                    class="h-8 w-8 animate-spin"
                    src="{{ bagisto_asset('images/spinner.svg') }}"
                />
            </div>

            <!-- Levels Table -->
            <div
                v-else-if="levels.length > 0"
                class="box-shadow rounded bg-white dark:bg-gray-900"
            >
                <div class="overflow-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                                    @lang('bonus::app.admin.levels.name')
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                                    @lang('bonus::app.admin.levels.cashback-percent')
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                                    @lang('bonus::app.admin.levels.threshold')
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                                    @lang('bonus::app.admin.levels.status')
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.datagrid.actions')
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="level in levels"
                                :key="level.id"
                                class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-950"
                            >
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    @{{ level.name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    @{{ level.cashback_percent }}%
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                    @{{ level.threshold_value }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span
                                        :class="level.is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-400'"
                                    >
                                        @{{ level.is_active ? translations.active : translations.inactive }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            :href="getEditUrl(level.id)"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            @lang('admin::app.datagrid.edit')
                                        </a>
                                        <button
                                            type="button"
                                            @click="confirmDelete(level)"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            @lang('admin::app.datagrid.delete')
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
            >
                <img
                    src="{{ bagisto_asset('images/empty-placeholders/customers.svg') }}"
                    class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                />
                <div class="flex flex-col items-center gap-1.5">
                    <p class="text-base font-semibold text-gray-400">
                        @lang('bonus::app.admin.levels.no-levels')
                    </p>
                    <p class="text-sm text-gray-400">
                        @lang('bonus::app.admin.settings.levels.empty-info')
                    </p>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-bonus-levels', {
            template: '#v-bonus-levels-template',

            props: {
                initialLevels: {
                    type: Array,
                    default: () => []
                }
            },

            data() {
                return {
                    levels: [],
                    isLoading: false,
                    translations: {
                        active: '{{ trans("admin::app.datagrid.active") }}',
                        inactive: '{{ trans("admin::app.datagrid.inactive") }}',
                        deleteWarning: '{{ trans("admin::app.datagrid.delete-warning") }}',
                        deleteSuccess: '{{ trans("bonus::app.admin.levels.delete-success") }}',
                    },
                }
            },

            mounted() {
                this.levels = this.initialLevels || [];
            },

            methods: {

                getEditUrl(id) {
                    return "{{ route('admin.bonus.levels.edit', ['id' => ':id']) }}".replace(':id', id);
                },

                confirmDelete(level) {
                    if (confirm(this.translations.deleteWarning)) {
                        this.deleteLevel(level.id);
                    }
                },

                deleteLevel(id) {
                    let self = this;

                    this.$axios.delete("{{ route('admin.bonus.levels.destroy', ['id' => ':id']) }}".replace(':id', id))
                        .then(function(response) {
                            self.$emitter.emit('add-flash', {
                                type: 'success',
                                message: self.translations.deleteSuccess
                            });
                            
                            // Remove level from list
                            self.levels = self.levels.filter(level => level.id !== id);
                            
                            // Reload page after a short delay to refresh data
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        })
                        .catch(function(error) {
                            self.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Ошибка удаления уровня'
                            });
                        });
                },
            },
        });
    </script>
@endPushOnce
