<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.stop-list.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.stop-list.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <button type="button"
                    id="delete-selected-btn"
                    onclick="deleteSelected()"
                    class="secondary-button hidden"
                    disabled>
                {{ __('newsletters::app.admin.stop-list.delete-selected') }}
            </button>
            <button type="button"
                    onclick="deleteAll()"
                    class="secondary-button {{ count($stopList) > 0 ? '' : 'hidden' }}">
                {{ __('newsletters::app.admin.stop-list.delete-all') ?? 'Удалить всё' }}
            </button>
            <a href="{{ route('admin.newsletters.stop-list.create') }}" class="primary-button">
                {{ __('newsletters::app.admin.stop-list.create') }}
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox"
                                   id="select-all-checkbox"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                   onchange="toggleSelectAll(this)">
                            <span class="ml-2">{{ __('newsletters::app.admin.stop-list.select_all') }}</span>
                        </label>
                    </th>
                    @php
                        $currentSortBy = $sortBy ?? 'id';
                        $currentSortDir = $sortDir ?? 'desc';
                    @endphp
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sortable-column">
                        @php
                            $isActive = $currentSortBy === 'id';
                            $nextDir = $isActive && $currentSortDir === 'desc' ? 'asc' : 'desc';
                        @endphp
                        <a href="{{ route('admin.newsletters.stop-list.index', ['sort_by' => 'id', 'sort_dir' => $nextDir]) }}" 
                           class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('newsletters::app.admin.stop-list.table.id') }}
                            @if($isActive)
                                <span class="ml-1">{{ $currentSortDir === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sortable-column">
                        @php
                            $isActive = $currentSortBy === 'phone_number';
                            $nextDir = $isActive && $currentSortDir === 'desc' ? 'asc' : 'desc';
                        @endphp
                        <a href="{{ route('admin.newsletters.stop-list.index', ['sort_by' => 'phone_number', 'sort_dir' => $nextDir]) }}" 
                           class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('newsletters::app.admin.stop-list.table.phone-number') }}
                            @if($isActive)
                                <span class="ml-1">{{ $currentSortDir === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sortable-column">
                        @php
                            $isActive = $currentSortBy === 'created_at';
                            $nextDir = $isActive && $currentSortDir === 'desc' ? 'asc' : 'desc';
                        @endphp
                        <a href="{{ route('admin.newsletters.stop-list.index', ['sort_by' => 'created_at', 'sort_dir' => $nextDir]) }}" 
                           class="flex items-center hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('newsletters::app.admin.stop-list.table.created-at') }}
                            @if($isActive)
                                <span class="ml-1">{{ $currentSortDir === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.stop-list.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($stopList as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <input type="checkbox"
                                   class="item-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                   value="{{ $item->id }}"
                                   onchange="updateDeleteButton()">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">{{ $item->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">{{ $item->phone_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">{{ $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.newsletters.stop-list.edit', $item->id) }}"
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                   title="{{ __('admin::app.datagrid.edit') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-edit"></span>
                                </a>
                                <button type="button"
                                        onclick="deleteStopList({{ $item->id }})"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        title="{{ __('admin::app.datagrid.delete') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-delete"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            <p>{{ __('newsletters::app.admin.stop-list.no-numbers-found') }} <a href="{{ route('admin.newsletters.stop-list.create') }}">{{ __('newsletters::app.admin.stop-list.add-first-number') }}</a></p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <style>
        /* Подсвечивание столбцов при наведении */
        .sortable-column {
            position: relative;
        }
        
        .sortable-column:hover {
            background-color: rgba(0, 0, 0, 0.1) !important;
        }
        
        .dark .sortable-column:hover {
            background-color: rgba(0, 0, 0, 0.3) !important;
        }
        
        table tbody tr:hover td {
            background-color: rgba(249, 250, 251, 0.5);
        }
        
        table tbody tr:hover td:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .dark table tbody tr:hover td {
            background-color: rgba(31, 41, 55, 0.5);
        }
        
        .dark table tbody tr:hover td:hover {
            background-color: rgba(59, 130, 246, 0.2);
        }
    </style>

    <script>
        function deleteStopList(id) {
            if (confirm('{{ __('newsletters::app.admin.stop-list.delete-confirm') ?? __('admin::app.datagrid.delete') }}?')) {
                fetch('{{ route('admin.newsletters.stop-list.destroy', ':id') }}'.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __('newsletters::app.admin.stop-list.delete-failed') ?? __('admin::app.datagrid.delete-failed') }}');
                });
            }
        }

        function toggleSelectAll(checkbox) {
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            itemCheckboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateDeleteButton();
        }

        function updateDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
            const deleteSelectedBtn = document.getElementById('delete-selected-btn');

            if (checkedBoxes.length > 0) {
                deleteSelectedBtn.classList.remove('hidden');
                deleteSelectedBtn.disabled = false;
            } else {
                deleteSelectedBtn.classList.add('hidden');
                deleteSelectedBtn.disabled = true;
            }

            // Update select-all checkbox state
            const allCheckboxes = document.querySelectorAll('.item-checkbox');
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            if (allCheckboxes.length > 0) {
                if (checkedBoxes.length === allCheckboxes.length) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else if (checkedBoxes.length > 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
            }
        }

        function deleteSelected() {
            const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('{{ __('admin::app.datagrid.no-records-selected') ?? 'Выберите записи для удаления' }}');
                return;
            }

            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            const count = ids.length;

            if (confirm('{{ __('admin::app.datagrid.delete-mass-confirm') ?? 'Вы уверены, что хотите удалить выбранные записи?' }} (' + count + ')')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const deleteSelectedBtn = document.getElementById('delete-selected-btn');

                deleteSelectedBtn.disabled = true;

                fetch('{{ route('admin.newsletters.stop-list.mass-destroy') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ ids: ids }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __('newsletters::app.admin.stop-list.mass-delete-failed') ?? 'Ошибка при удалении записей' }}');
                    deleteSelectedBtn.disabled = false;
                });
            }
        }

        function deleteAll() {
            const totalCount = {{ count($stopList) }};
            if (totalCount === 0) {
                return;
            }

            if (confirm('{{ __('newsletters::app.admin.stop-list.delete-all-confirm') ?? 'Вы уверены, что хотите удалить все записи?' }} (' + totalCount + ')')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const deleteAllBtn = document.querySelector('button[onclick="deleteAll()"]');

                if (deleteAllBtn) {
                    deleteAllBtn.disabled = true;
                }

                fetch('{{ route('admin.newsletters.stop-list.destroy-all') }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __('newsletters::app.admin.stop-list.delete-all-failed') ?? 'Ошибка при удалении всех записей' }}');
                    if (deleteAllBtn) {
                        deleteAllBtn.disabled = false;
                    }
                });
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateDeleteButton();
        });
    </script>
</x-admin::layouts>
