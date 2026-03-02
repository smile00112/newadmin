<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.owners.title') }}
    </x-slot:title>

    @php
        $currentAdmin = auth()->guard('admin')->user();
        $canImpersonateOwners = $currentAdmin
            && $currentAdmin->role
            && $currentAdmin->role->permission_type === 'all'
            && ! $currentAdmin->company_id;
    @endphp

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.owners.title') }}
        </p>
        <div class="flex items-center gap-x-2.5">
            @if($canImpersonateOwners)
                <button
                    type="button"
                    id="deleteSelectedOwnersBtn"
                    onclick="deleteSelectedOwners()"
                    class="secondary-button"
                    disabled
                >
                    {{ __('newsletters::app.admin.owners.delete-selected') }}
                </button>
            @endif
            <a href="{{ route('admin.newsletters.owners.create') }}" class="primary-button">
                {{ __('newsletters::app.admin.owners.create-button') }}
            </a>
        </div>
    </div>

    <div class="overflow-x-auto mt-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                @if($canImpersonateOwners)
                    <th class="px-6 py-3 text-left">
                        <input
                            type="checkbox"
                            id="selectAllOwners"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            onchange="toggleSelectAllOwners(this)"
                        >
                    </th>
                @endif
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.email') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.company') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.balance') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.status') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.created_at') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.common.actions.title') }}</th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($owners as $owner)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    @if($canImpersonateOwners)
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if((int) $owner->id !== (int) $currentAdmin->id)
                                <input
                                    type="checkbox"
                                    class="owner-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    value="{{ $owner->id }}"
                                    onchange="updateOwnersDeleteButton()"
                                >
                            @endif
                        </td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $owner->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $owner->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $owner->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $owner->company ? $owner->company->name : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($owner->company && $owner->company->account)
                            <span class="{{ $owner->company->account->balance <= 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                {{ number_format($owner->company->account->balance, 2) }}
                            </span>
                        @else
                            <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $owner->status ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                            {{ $owner->status ? __('newsletters::app.admin.owners.active') : __('newsletters::app.admin.owners.inactive') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $owner->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-x-2.5">
                            @if($canImpersonateOwners && (int) $owner->id !== (int) $currentAdmin->id)
                                <form method="POST" action="{{ route('admin.newsletters.owners.impersonate', $owner->id) }}" onsubmit="return confirm('{{ __('newsletters::app.admin.owners.impersonation-start-confirm') }}')" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            title="{{ __('newsletters::app.admin.owners.login-as-owner') }}">
                                        <span class="cursor-pointer rounded-md p-1.5 text-xs font-semibold transition-all hover:bg-gray-200 dark:hover:bg-gray-800">
                                            {{ __('newsletters::app.admin.owners.login-as-owner-short') }}
                                        </span>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.newsletters.owners.edit', $owner->id) }}"
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                               title="{{ __('admin::app.datagrid.edit') }}">
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-edit"></span>
                            </a>
                            <form method="POST" action="{{ route('admin.newsletters.owners.resend-email', $owner->id) }}" onsubmit="return confirm('{{ __('newsletters::app.admin.owners.resend-email-confirm') }}')" class="inline">
                                @csrf
                                <button type="submit"
                                        class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                                        title="{{ __('newsletters::app.admin.owners.resend-email-title') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-mail"></span>
                                </button>
                            </form>
                            <button
                                type="button"
                                onclick="toggleStatus({{ $owner->id }})"
                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                title="{{ __('newsletters::app.admin.owners.status') }}">
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-{{ $owner->status ? 'cancel' : 'check' }}"></span>
                            </button>
                            @if($owner->company && $owner->company->account)
                                <button
                                    type="button"
                                    onclick="openTopupModal({{ $owner->id }}, '{{ $owner->name }}', '{{ $owner->company->name }}')"
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                    title="{{ __('newsletters::app.admin.owners.topup-title') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-money"></span>
                                </button>
                            @endif
                            <button
                                type="button"
                                onclick="deleteOwner({{ $owner->id }})"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                title="{{ __('admin::app.datagrid.delete') }}"
                            >
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-trash"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canImpersonateOwners ? 9 : 8 }}" class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.owners.no-owners') }}</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Topup Modal -->
    <div id="topupModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ __('newsletters::app.admin.owners.topup-title') }} - <span id="ownerName"></span>
                </h3>
                <button onclick="closeTopupModal()" class="text-gray-500 hover:text-gray-700">
                    <span class="icon-close"></span>
                </button>
            </div>

            <form id="topupForm" method="POST">
                @csrf
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        {{ __('newsletters::app.admin.owners.topup-amount') }}
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="number"
                        name="amount"
                        step="0.01"
                        min="0.01"
                        rules="required|numeric|min:0.01"
                        :label="trans('newsletters::app.admin.owners.topup-amount')"
                        :placeholder="trans('newsletters::app.admin.account.amount-placeholder')"
                    />

                    <x-admin::form.control-group.error control-name="amount" />
                </x-admin::form.control-group>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        {{ __('newsletters::app.admin.owners.topup-notes') }}
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="notes"
                        rules="max:500"
                        :label="trans('newsletters::app.admin.owners.topup-notes')"
                        :placeholder="trans('newsletters::app.admin.account.notes-placeholder')"
                    />

                    <x-admin::form.control-group.error control-name="notes" />
                </x-admin::form.control-group>

                <div class="flex items-center gap-x-2.5 mt-4">
                    <button type="submit" class="primary-button">
                        {{ __('newsletters::app.admin.account.topup-button') }}
                    </button>
                    <button type="button" onclick="closeTopupModal()" class="secondary-button">
                        {{ __('newsletters::app.common.actions.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function getSelectedOwnerIds() {
            return Array.from(document.querySelectorAll('.owner-checkbox:checked')).map((checkbox) => checkbox.value);
        }

        function toggleSelectAllOwners(masterCheckbox) {
            document.querySelectorAll('.owner-checkbox').forEach((checkbox) => {
                checkbox.checked = masterCheckbox.checked;
            });

            updateOwnersDeleteButton();
        }

        function updateOwnersDeleteButton() {
            const selectedIds = getSelectedOwnerIds();
            const deleteButton = document.getElementById('deleteSelectedOwnersBtn');
            const selectAll = document.getElementById('selectAllOwners');
            const allCheckboxes = document.querySelectorAll('.owner-checkbox');

            if (deleteButton) {
                deleteButton.disabled = selectedIds.length === 0;
            }

            if (selectAll) {
                selectAll.checked = allCheckboxes.length > 0 && selectedIds.length === allCheckboxes.length;
            }
        }

        function deleteOwner(ownerId) {
            if (!confirm('{{ __('newsletters::app.admin.owners.delete-confirm') }}')) {
                return;
            }

            fetch('{{ route("admin.newsletters.owners.delete", ":id") }}'.replace(':id', ownerId), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                        return;
                    }

                    alert(data.message || '{{ __('newsletters::app.admin.owners.delete-failed') }}');
                })
                .catch(() => {
                    alert('{{ __('newsletters::app.admin.owners.delete-failed') }}');
                });
        }

        function deleteSelectedOwners() {
            const selectedIds = getSelectedOwnerIds();

            if (selectedIds.length === 0) {
                return;
            }

            if (!confirm('{{ __('newsletters::app.admin.owners.mass-delete-confirm') }}')) {
                return;
            }

            fetch('{{ route('admin.newsletters.owners.mass-destroy') }}', {
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

                    alert(data.message || '{{ __('newsletters::app.admin.owners.mass-delete-failed') }}');
                })
                .catch(() => {
                    alert('{{ __('newsletters::app.admin.owners.mass-delete-failed') }}');
                });
        }

        function toggleStatus(ownerId) {
            if (!confirm('{{ __('newsletters::app.admin.owners.delete-confirm') }}')) {
                return;
            }

            fetch('{{ route("admin.newsletters.owners.toggle-status", ":id") }}'.replace(':id', ownerId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '{{ __('newsletters::app.admin.owners.delete-failed') }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __('newsletters::app.admin.owners.delete-failed') }}');
            });
        }

        function openTopupModal(ownerId, ownerName, companyName) {
            document.getElementById('ownerName').textContent = ownerName + ' (' + companyName + ')';
            document.getElementById('topupForm').action = '{{ route("admin.newsletters.owners.topup", ":id") }}'.replace(':id', ownerId);
            document.getElementById('topupModal').classList.remove('hidden');
            document.getElementById('topupModal').classList.add('flex');
        }

        function closeTopupModal() {
            document.getElementById('topupModal').classList.add('hidden');
            document.getElementById('topupModal').classList.remove('flex');
            document.getElementById('topupForm').reset();
        }
    </script>
</x-admin::layouts>

