<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.account-warmings.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap mb-6">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.account-warmings.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.account-warmings.create') }}" class="primary-button">
                {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.account-warmings.title') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.id') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.account-warmings.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.account-warmings.accounts-count') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.account-warmings.active') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.created_at') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($warmings as $warming)
                        <tr data-warming-id="{{ $warming->id }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $warming->id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                <a href="{{ route('admin.newsletters.account-warmings.edit', $warming->id) }}">
                                    {{ $warming->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $warming->whatsapp_instances_count ?? count($warming->selected_account_ids ?? []) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $warming->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $warming->active ? __('newsletters::app.admin.account-warmings.is-active') : __('newsletters::app.admin.account-warmings.not-active') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" data-field="status">
                                {{ __('newsletters::app.admin.account-warmings.status.' . $warming->status) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $warming->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if(!$warming->active)
                                        <button onclick="startWarming({{ $warming->id }})"
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                                title="{{ __('newsletters::app.admin.account-warmings.start-warming') }}"
                                                id="start-btn-{{ $warming->id }}">
                                            <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-arrow-left bg-[#B5DCB4]"></span>
                                        </button>
                                    @else
                                        <button onclick="pauseWarming({{ $warming->id }})"
                                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                                title="{{ __('newsletters::app.admin.account-warmings.pause-warming') }}"
                                                id="pause-btn-{{ $warming->id }}">
                                            <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-pause bg-[#FFD700]"></span>
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.newsletters.account-warmings.edit', $warming->id) }}"
                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="{{ __('newsletters::app.common.actions.edit') }}">
                                        <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-edit"></span>
                                    </a>
                                    <button onclick="deleteWarming({{ $warming->id }})"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="{{ __('newsletters::app.common.actions.delete') }}"
                                    >
                                        <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-delete"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="mx-auto h-12 w-12 text-gray-400 icon-inbox"></span>
                                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ __('newsletters::app.admin.account-warmings.no-warmings') }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('newsletters::app.common.messages.get_started') }}
                                    </p>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.newsletters.account-warmings.create') }}"
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.account-warmings.title') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>

    <script>
        function startWarming(id) {
            if (confirm('{{ __("newsletters::app.admin.account-warmings.start-confirm") }}')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                const startButton = document.getElementById('start-btn-' + id);

                if (!csrfToken) {
                    console.error('CSRF token not found!');
                    alert('Security token not found. Please refresh the page and try again.');
                    return;
                }

                if (startButton) {
                    startButton.disabled = true;
                    startButton.style.opacity = '0.5';
                }

                fetch('{{ route("admin.newsletters.account-warmings.start", ":id") }}'.replace(':id', id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                        if (startButton) {
                            startButton.disabled = false;
                            startButton.style.opacity = '1';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || '{{ __("newsletters::app.admin.account-warmings.warming-start-failed") }}');
                    if (startButton) {
                        startButton.disabled = false;
                        startButton.style.opacity = '1';
                    }
                });
            }
        }

        function pauseWarming(id) {
            if (confirm('{{ __("newsletters::app.admin.account-warmings.pause-confirm") }}')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                const pauseButton = document.getElementById('pause-btn-' + id);

                if (!csrfToken) {
                    console.error('CSRF token not found!');
                    alert('Security token not found. Please refresh the page and try again.');
                    return;
                }

                if (pauseButton) {
                    pauseButton.disabled = true;
                    pauseButton.style.opacity = '0.5';
                }

                fetch('{{ route("admin.newsletters.account-warmings.pause", ":id") }}'.replace(':id', id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                        if (pauseButton) {
                            pauseButton.disabled = false;
                            pauseButton.style.opacity = '1';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || '{{ __("newsletters::app.admin.account-warmings.warming-pause-failed") }}');
                    if (pauseButton) {
                        pauseButton.disabled = false;
                        pauseButton.style.opacity = '1';
                    }
                });
            }
        }

        function deleteWarming(id) {
            if (confirm('{{ __("newsletters::app.admin.account-warmings.delete-confirm") }}')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');

                if (!csrfToken) {
                    console.error('CSRF token not found!');
                    alert('Security token not found. Please refresh the page and try again.');
                    return;
                }

                fetch('{{ route("admin.newsletters.account-warmings.destroy", ":id") }}'.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `HTTP error! status: ${response.status}`);
                        });
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
                    alert('{{ __("newsletters::app.admin.account-warmings.delete-failed") }}: ' + error.message);
                });
            }
        }
    </script>
</x-admin::layouts>


