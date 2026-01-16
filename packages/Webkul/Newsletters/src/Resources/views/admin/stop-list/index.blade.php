<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.stop-list.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap mb-6">
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

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox"
                                   id="select-all-checkbox"
                                   class="peer hidden"
                                   onchange="toggleSelectAll(this)">
                            <span class="icon-uncheckbox cursor-pointer rounded-md text-2xl peer-checked:icon-checked peer-checked:text-blue-600 peer-indeterminate:icon-checkbox-partial peer-indeterminate:text-blue-600"></span>
                            <span class="ml-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('newsletters::app.admin.stop-list.select_all') }}</span>
                        </label>
                    </th>
                    @php
                        $currentSortBy = $sortBy ?? 'id';
                        $currentSortDir = $sortDir ?? 'desc';
                    @endphp
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        @php
                            $isActive = $currentSortBy === 'id';
                            $nextDir = $isActive && $currentSortDir === 'desc' ? 'asc' : 'desc';
                        @endphp
                        <a href="{{ route('admin.newsletters.stop-list.index', ['sort_by' => 'id', 'sort_dir' => $nextDir]) }}" 
                           class="flex items-center cursor-pointer text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white {{ $isActive ? 'font-medium text-gray-800 dark:text-white' : '' }}">
                            {{ __('newsletters::app.admin.stop-list.table.id') }}
                            @if($isActive)
                                <span class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5 {{ $currentSortDir === 'asc' ? 'icon-down-stat' : 'icon-up-stat' }}"></span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        @php
                            $isActive = $currentSortBy === 'phone_number';
                            $nextDir = $isActive && $currentSortDir === 'desc' ? 'asc' : 'desc';
                        @endphp
                        <a href="{{ route('admin.newsletters.stop-list.index', ['sort_by' => 'phone_number', 'sort_dir' => $nextDir]) }}" 
                           class="flex items-center cursor-pointer text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white {{ $isActive ? 'font-medium text-gray-800 dark:text-white' : '' }}">
                            {{ __('newsletters::app.admin.stop-list.table.phone-number') }}
                            @if($isActive)
                                <span class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5 {{ $currentSortDir === 'asc' ? 'icon-down-stat' : 'icon-up-stat' }}"></span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        @php
                            $isActive = $currentSortBy === 'created_at';
                            $nextDir = $isActive && $currentSortDir === 'desc' ? 'asc' : 'desc';
                        @endphp
                        <a href="{{ route('admin.newsletters.stop-list.index', ['sort_by' => 'created_at', 'sort_dir' => $nextDir]) }}" 
                           class="flex items-center cursor-pointer text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white {{ $isActive ? 'font-medium text-gray-800 dark:text-white' : '' }}">
                            {{ __('newsletters::app.admin.stop-list.table.created-at') }}
                            @if($isActive)
                                <span class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5 {{ $currentSortDir === 'asc' ? 'icon-down-stat' : 'icon-up-stat' }}"></span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.stop-list.table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($stopList as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox"
                                       class="item-checkbox peer hidden"
                                       value="{{ $item->id }}"
                                       onchange="updateDeleteButton()">
                                <span class="icon-uncheckbox cursor-pointer rounded-md text-2xl peer-checked:icon-checked peer-checked:text-blue-600"></span>
                            </label>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item->phone_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.newsletters.stop-list.edit', $item->id) }}"
                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                   title="{{ __('admin::app.datagrid.edit') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-edit"></span>
                                </a>
                                <button type="button"
                                        onclick="deleteStopList({{ $item->id }})"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        title="{{ __('admin::app.datagrid.delete') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-delete"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <span class="mx-auto h-12 w-12 text-gray-400 icon-inbox"></span>
                                <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ __('newsletters::app.admin.stop-list.no-numbers-found') }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    <a href="{{ route('admin.newsletters.stop-list.create') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ __('newsletters::app.admin.stop-list.add-first-number') }}
                                    </a>
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

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
