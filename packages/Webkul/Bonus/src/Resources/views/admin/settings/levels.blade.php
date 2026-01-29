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
                <button
                    type="button"
                    @click="openCreateModal"
                    class="primary-button"
                >
                    @lang('bonus::app.admin.levels.create')
                </button>
            </div>

            <!-- Create/Edit Level Modal -->
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-[10001] flex items-center justify-center bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full"
                @click.self="closeCreateModal"
            >
                <div class="relative top-10 mx-auto p-5 border w-11/12 shadow-lg rounded-md bg-white dark:bg-gray-800 max-h-[90vh] overflow-y-auto md:w-3/4 lg:w-2/3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            @{{ editingLevelId ? translations.editTitle : translations.createTitle }}
                        </h3>
                        <button
                            type="button"
                            @click="closeCreateModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <span class="icon-cross text-2xl"></span>
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="mt-3">
                        <form @submit.prevent="saveLevel">
                            <div class="space-y-4">
                                <!-- Name Field -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        @lang('bonus::app.admin.levels.name')
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="formData.name"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': errors.name }"
                                    />
                                    <p v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        @{{ errors.name[0] }}
                                    </p>
                                </div>

                                <!-- Cashback Percent Field -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        @lang('bonus::app.admin.levels.cashback-percent')
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        v-model="formData.cashback_percent"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': errors.cashback_percent }"
                                    />
                                    <p v-if="errors.cashback_percent" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        @{{ errors.cashback_percent[0] }}
                                    </p>
                                </div>

                                <!-- Threshold Value Field -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        @lang('bonus::app.admin.levels.threshold')
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        v-model="formData.threshold_value"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': errors.threshold_value }"
                                    />
                                    <p v-if="errors.threshold_value" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        @{{ errors.threshold_value[0] }}
                                    </p>
                                </div>

                                <!-- Sort Order Field -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        @lang('bonus::app.admin.levels.sort-order')
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        v-model="formData.sort_order"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': errors.sort_order }"
                                    />
                                    <p v-if="errors.sort_order" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        @{{ errors.sort_order[0] }}
                                    </p>
                                </div>

                                <!-- Is Active Field -->
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="formData.is_active"
                                        :checked="formData.is_active"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <label class="ml-2 block text-sm text-gray-900 dark:text-gray-300 cursor-pointer">
                                        @lang('bonus::app.admin.levels.is-active')
                                    </label>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="flex justify-end gap-x-2 pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                                <button
                                    type="button"
                                    @click="closeCreateModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                                >
                                    @lang('admin::app.datagrid.cancel')
                                </button>
                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="isSubmitting">
                                        <img
                                            class="h-5 w-5 animate-spin inline-block"
                                            src="{{ bagisto_asset('images/spinner.svg') }}"
                                        />
                                    </span>
                                    <span v-else>
                                        @lang('admin::app.datagrid.save')
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
                                        <button
                                            type="button"
                                            @click="openEditModal(level)"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            @lang('admin::app.datagrid.edit')
                                        </button>
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
                    showCreateModal: false,
                    editingLevelId: null,
                    isSubmitting: false,
                    formData: {
                        name: '',
                        cashback_percent: '',
                        threshold_value: '',
                        sort_order: 0,
                        is_active: true,
                    },
                    errors: {},
                    translations: {
                        active: '{{ trans("admin::app.datagrid.active") }}',
                        inactive: '{{ trans("admin::app.datagrid.inactive") }}',
                        deleteWarning: '{{ trans("admin::app.datagrid.delete-warning") }}',
                        deleteSuccess: '{{ trans("bonus::app.admin.levels.delete-success") }}',
                        createSuccess: '{{ trans("bonus::app.admin.levels.create-success") }}',
                        updateSuccess: '{{ trans("bonus::app.admin.levels.update-success") }}',
                        createTitle: '{{ trans("bonus::app.admin.levels.create") }}',
                        editTitle: '{{ trans("bonus::app.admin.levels.edit") }}',
                    },
                }
            },

            mounted() {
                this.levels = this.initialLevels || [];
                
                // Handle Escape key to close modal
                const self = this;
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && self.showCreateModal) {
                        self.closeCreateModal();
                    }
                });
            },

            methods: {
                openCreateModal() {
                    this.editingLevelId = null;
                    this.showCreateModal = true;
                    this.resetForm();
                },

                openEditModal(level) {
                    this.editingLevelId = level.id;
                    this.showCreateModal = true;
                    this.formData = {
                        name: level.name || '',
                        cashback_percent: level.cashback_percent || '',
                        threshold_value: level.threshold_value || '',
                        sort_order: level.sort_order || 0,
                        is_active: level.is_active !== undefined ? level.is_active : true,
                    };
                    this.errors = {};
                },

                closeCreateModal() {
                    this.showCreateModal = false;
                    this.editingLevelId = null;
                    this.resetForm();
                },

                resetForm() {
                    this.formData = {
                        name: '',
                        cashback_percent: '',
                        threshold_value: '',
                        sort_order: 0,
                        is_active: true,
                    };
                    this.errors = {};
                },

                saveLevel() {
                    this.isSubmitting = true;
                    this.errors = {};

                    let self = this;
                    const isEditing = this.editingLevelId !== null;
                    
                    // Prepare form data
                    const formData = { ...this.formData };
                    if (isEditing) {
                        formData._method = 'PUT';
                    }
                    
                    const url = isEditing 
                        ? "{{ route('admin.bonus.levels.update', ['id' => ':id']) }}".replace(':id', this.editingLevelId)
                        : "{{ route('admin.bonus.levels.store') }}";

                    this.$axios.post(url, formData)
                        .then(function(response) {
                            self.isSubmitting = false;
                            
                            self.$emitter.emit('add-flash', {
                                type: 'success',
                                message: isEditing ? self.translations.updateSuccess : self.translations.createSuccess
                            });

                            // Reload page to refresh levels list
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        })
                        .catch(function(error) {
                            self.isSubmitting = false;

                            if (error.response?.data?.errors) {
                                self.errors = error.response.data.errors;
                            } else {
                                self.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || (isEditing ? 'Ошибка обновления уровня' : 'Ошибка создания уровня')
                                });
                            }
                        });
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
