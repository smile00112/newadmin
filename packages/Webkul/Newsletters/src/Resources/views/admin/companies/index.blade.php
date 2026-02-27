<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.companies.title') }}
    </x-slot:title>

    @php
        $currentAdmin = auth()->guard('admin')->user();
        $canBulkDeleteCompanies = $currentAdmin
            && $currentAdmin->role
            && $currentAdmin->role->permission_type === 'all'
            && ! $currentAdmin->company_id;
    @endphp

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.companies.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            @if($canBulkDeleteCompanies)
                <button
                    type="button"
                    id="deleteSelectedCompaniesBtn"
                    onclick="deleteSelectedCompanies()"
                    class="secondary-button"
                    disabled
                >
                    {{ __('newsletters::app.admin.companies.delete-selected') }}
                </button>
            @endif

            <a href="{{ route('admin.newsletters.companies.create') }}" class="primary-button">
                {{ __('newsletters::app.admin.datagrid.add') }}
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                @if($canBulkDeleteCompanies)
                    <th class="px-6 py-3 text-left">
                        <input
                            type="checkbox"
                            id="selectAllCompanies"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            onchange="toggleSelectAllCompanies(this)"
                        >
                    </th>
                @endif
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.slug') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.status') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.created_at') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.common.actions.title') }}</th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($companies as $company)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    @if($canBulkDeleteCompanies)
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <input
                                type="checkbox"
                                class="company-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                value="{{ $company->id }}"
                                onchange="updateCompaniesDeleteButton()"
                            >
                        </td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $company->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $company->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $company->slug }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $company->is_active ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                            {{ $company->is_active ? __('newsletters::app.admin.companies.active') : __('newsletters::app.admin.companies.inactive') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $company->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-x-2.5">
                            <a href="{{ route('admin.newsletters.companies.edit', $company->id) }}" 
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                               title="{{ __('admin::app.datagrid.edit') }}">
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-edit"></span>
                            </a>
                            <form method="POST" action="{{ route('admin.newsletters.companies.destroy', $company->id) }}" onsubmit="return confirm('{{ __('newsletters::app.admin.companies.delete-confirm') }}')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        title="{{ __('admin::app.datagrid.delete') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-trash"></span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canBulkDeleteCompanies ? 7 : 6 }}" class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('newsletters::app.common.messages.no_data') }}</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($canBulkDeleteCompanies)
        <script>
            function getSelectedCompanyIds() {
                return Array.from(document.querySelectorAll('.company-checkbox:checked')).map((checkbox) => checkbox.value);
            }

            function toggleSelectAllCompanies(masterCheckbox) {
                document.querySelectorAll('.company-checkbox').forEach((checkbox) => {
                    checkbox.checked = masterCheckbox.checked;
                });

                updateCompaniesDeleteButton();
            }

            function updateCompaniesDeleteButton() {
                const selectedIds = getSelectedCompanyIds();
                const deleteButton = document.getElementById('deleteSelectedCompaniesBtn');
                const selectAll = document.getElementById('selectAllCompanies');
                const allCheckboxes = document.querySelectorAll('.company-checkbox');

                if (deleteButton) {
                    deleteButton.disabled = selectedIds.length === 0;
                }

                if (selectAll) {
                    selectAll.checked = allCheckboxes.length > 0 && selectedIds.length === allCheckboxes.length;
                }
            }

            function deleteSelectedCompanies() {
                const selectedIds = getSelectedCompanyIds();

                if (selectedIds.length === 0) {
                    return;
                }

                if (!confirm('{{ __('newsletters::app.admin.companies.mass-delete-confirm') }}')) {
                    return;
                }

                fetch('{{ route('admin.newsletters.companies.mass-destroy') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        ids: selectedIds,
                    }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                            return;
                        }

                        alert(data.message || '{{ __('newsletters::app.admin.companies.mass-delete-failed') }}');
                    })
                    .catch(() => {
                        alert('{{ __('newsletters::app.admin.companies.mass-delete-failed') }}');
                    });
            }
        </script>
    @endif
</x-admin::layouts>

