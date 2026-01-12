<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.registration-requests.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.registration-requests.title') }}
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.registration-requests.name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.registration-requests.email') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.registration-requests.phone') }}</th>
                {{--
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.registration-requests.plan') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.registration-requests.status') }}</th>
                --}}
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.registration-requests.created_at') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.common.actions.title') }}</th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($requests as $request)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $request->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $request->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $request->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $request->phone }}</td>
                    {{--
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        @if($request->plan)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                {{ ucfirst($request->plan) }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($request->status === 'pending')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                {{ __('newsletters::app.admin.registration-requests.status-pending') }}
                            </span>
                        @elseif($request->status === 'processed')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                {{ __('newsletters::app.admin.registration-requests.status-processed') }}
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                {{ __('newsletters::app.admin.registration-requests.status-rejected') }}
                            </span>
                        @endif
                    </td>
                    --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $request->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-x-2.5">
                            <a href="{{ route('admin.newsletters.registration-requests.edit', $request->id) }}"
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                               title="{{ __('admin::app.datagrid.edit') }}">
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-edit"></span>
                            </a>
                            <button type="button"
                                    onclick="deleteRequest({{ $request->id }})"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    title="{{ __('admin::app.datagrid.delete') }}">
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-delete"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('newsletters::app.common.messages.no_data') }}</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function deleteRequest(id) {
            if (confirm('{{ __('newsletters::app.admin.registration-requests.delete-confirm') }}')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    alert('Security token not found. Please refresh the page.');
                    return;
                }

                fetch('{{ route('admin.newsletters.registration-requests.destroy', ':id') }}'.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __('newsletters::app.admin.registration-requests.delete-failed') }}');
                });
            }
        }
    </script>
</x-admin::layouts>




