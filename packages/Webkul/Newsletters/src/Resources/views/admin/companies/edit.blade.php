<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.companies.edit-title') }}
    </x-slot:title>

    <form method="POST" action="{{ route('admin.newsletters.companies.update', $company->id) }}">
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                {{ __('newsletters::app.admin.companies.edit-title') }}
            </p>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.newsletters.companies.index') }}" class="secondary-button">
                    {{ __('newsletters::app.common.actions.cancel') }}
                </a>
                <button type="submit" class="primary-button">
                    {{ __('newsletters::app.common.actions.save') }}
                </button>
            </div>
        </div>

        <div class="p-3 mt-4 box-shadow rounded bg-white p-6 dark:bg-gray-900">
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.name') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $company->name) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.slug') }}
                    </label>
                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        value="{{ old('slug', $company->slug) }}"
                        placeholder="{{ __('newsletters::app.admin.companies.slug-placeholder') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.companies.slug-help') }}</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.description') }}
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors resize-y"
                    >{{ old('description', $company->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                        >
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('newsletters::app.admin.companies.is_active') }}
                        </span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Users linked to company --}}
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                {{ __('newsletters::app.admin.companies.users-title') }}
            </h3>
            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.users-name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.users-email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.users-role') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.users-status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.common.actions.title') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($company->admins as $adminUser)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $adminUser->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $adminUser->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    @if($adminUser->role && $adminUser->role->permission_type === 'all')
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                            {{ __('newsletters::app.admin.companies.users-role-owner') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ optional($adminUser->role)->name ?? __('newsletters::app.admin.companies.users-role-manager') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $adminUser->status ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ $adminUser->status ? __('newsletters::app.admin.owners.active') : __('newsletters::app.admin.owners.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                    <div class="flex items-center justify-end gap-x-2">
                                        @if($adminUser->role && $adminUser->role->permission_type === 'all')
                                            <a href="{{ route('admin.newsletters.owners.edit', $adminUser->id) }}"
                                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                               title="{{ __('admin::app.datagrid.edit') }}">
                                                <span class="icon-edit text-xl"></span>
                                            </a>
                                            <button type="button"
                                                    onclick="deleteCompanyUser({{ $adminUser->id }}, 'owner', '{{ addslashes($adminUser->name) }}')"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    title="{{ __('admin::app.datagrid.delete') }}">
                                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-delete"></span>
                                            </button>
                                        @else
                                            <a href="{{ route('admin.newsletters.managers.edit', $adminUser->id) }}"
                                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                               title="{{ __('admin::app.datagrid.edit') }}">
                                                <span class="icon-edit text-xl"></span>
                                            </a>
                                            <button type="button"
                                                    onclick="deleteCompanyUser({{ $adminUser->id }}, 'manager', '{{ addslashes($adminUser->name) }}')"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    title="{{ __('admin::app.datagrid.delete') }}">
                                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-delete"></span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('newsletters::app.admin.companies.users-empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('newsletters::app.admin.companies.users-help') }}
            </p>
        </div>
    </form>

    <script>
        function deleteCompanyUser(userId, type, userName) {
            const message = '{{ __('newsletters::app.admin.companies.users-delete-confirm') }}'.replace(':name', userName);
            if (!confirm(message)) {
                return;
            }

            const url = type === 'owner'
                ? '{{ route("admin.newsletters.owners.delete", ":id") }}'.replace(':id', userId)
                : '{{ route("admin.newsletters.managers.destroy", ":id") }}'.replace(':id', userId);

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
            .then(response => response.json().then(data => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                if (ok && data.success !== false) {
                    location.reload();
                } else {
                    alert(data.message || '{{ __('newsletters::app.admin.companies.users-delete-failed') }}');
                }
            })
            .catch(() => {
                alert('{{ __('newsletters::app.admin.companies.users-delete-failed') }}');
            });
        }
    </script>
</x-admin::layouts>

