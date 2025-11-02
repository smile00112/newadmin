<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.stop-list.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.stop-list.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.stop-list.create') }}" class="primary-button">
                {{ __('newsletters::app.admin.stop-list.create') }}
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('newsletters::app.admin.stop-list.table.id') }}</th>
                    <th>{{ __('newsletters::app.admin.stop-list.table.phone-number') }}</th>
                    <th>{{ __('newsletters::app.admin.stop-list.table.created-at') }}</th>
                    <th>{{ __('newsletters::app.admin.stop-list.table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stopList as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->phone_number }}</td>
                        <td>{{ $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                        <td>
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
                        <td colspan="4" class="text-center">
                            <p>{{ __('newsletters::app.admin.stop-list.no-numbers-found') }} <a href="{{ route('admin.newsletters.stop-list.create') }}">{{ __('newsletters::app.admin.stop-list.add-first-number') }}</a></p>
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
    </script>
</x-admin::layouts>
