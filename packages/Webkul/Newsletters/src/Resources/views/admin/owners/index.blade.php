<x-admin::layouts>
    <x-slot:title>
        {{ $context['type'] === 'super_admin' ? __('newsletters::app.admin.owners.title') : __('newsletters::app.admin.managers.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ $context['type'] === 'super_admin' ? __('newsletters::app.admin.owners.title') : __('newsletters::app.admin.managers.title') }}
        </p>
        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.owners.create') }}" class="primary-button">
                {{ $context['type'] === 'super_admin' ? __('newsletters::app.admin.owners.create-button') : __('newsletters::app.admin.managers.create-title') }}
            </a>
        </div>
    </div>

    <div class="overflow-x-auto mt-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.email') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.role') }}</th>
                @if($context['type'] === 'super_admin')
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.company') }}</th>
                @endif
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.status') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.owners.created_at') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.common.actions.title') }}</th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $user->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $user->role ? $user->role->name : '-' }}
                    </td>
                    @if($context['type'] === 'super_admin')
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $user->company ? $user->company->name : '-' }}
                    </td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->status ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                            {{ $user->status ? __('newsletters::app.admin.owners.active') : __('newsletters::app.admin.owners.inactive') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-x-2.5">
                            <a href="{{ route('admin.newsletters.owners.edit', $user->id) }}"
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                               title="{{ __('admin::app.datagrid.edit') }}">
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-edit"></span>
                            </a>
                            @if($context['type'] === 'super_admin' && $context['can_resend_email'])
                            <form method="POST" action="{{ route('admin.newsletters.owners.resend-email', $user->id) }}" onsubmit="return confirm('{{ __('newsletters::app.admin.owners.resend-email-confirm') }}')" class="inline">
                                @csrf
                                <button type="submit"
                                        class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                                        title="{{ __('newsletters::app.admin.owners.resend-email-title') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-mail"></span>
                                </button>
                            </form>
                            @endif
                            @if($context['type'] === 'super_admin' && $user->status)
                            <form method="POST" action="{{ route('admin.newsletters.owners.impersonate', $user->id) }}" onsubmit="return confirm('{{ __('newsletters::app.admin.owners.impersonate-confirm', ['name' => $user->name]) }}')" class="inline">
                                @csrf
                                <button type="submit"
                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                        title="{{ __('newsletters::app.admin.owners.impersonate-title') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-login"></span>
                                </button>
                            </form>
                            @endif
                            <button
                                type="button"
                                onclick="toggleStatus({{ $user->id }})"
                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                title="{{ __('newsletters::app.admin.owners.status') }}">
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-{{ $user->status ? 'cancel' : 'checked' }}"></span>
                            </button>
{{--                            <form method="POST" action="{{ route('admin.newsletters.owners.delete', $user->id) }}" onsubmit="return confirm('{{ $context['type'] === 'super_admin' ? __('newsletters::app.admin.owners.delete-confirm') : __('newsletters::app.admin.managers.delete-confirm') }}')" class="inline">--}}
{{--                                @csrf--}}
{{--                                @method('DELETE')--}}
{{--                                <button type="submit"--}}
{{--                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"--}}
{{--                                        title="{{ __('admin::app.datagrid.delete') }}">--}}
{{--                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-delete"></span>--}}
{{--                                </button>--}}
{{--                            </form>--}}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $context['type'] === 'super_admin' ? '8' : '7' }}" class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $context['type'] === 'super_admin' ? __('newsletters::app.admin.owners.no-owners') : __('newsletters::app.admin.managers.no-managers') }}</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function toggleStatus(userId) {
            fetch('{{ route("admin.newsletters.owners.toggle-status", ":id") }}'.replace(':id', userId), {
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
    </script>
</x-admin::layouts>

