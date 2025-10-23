<x-admin::layouts>
    {{--
    <script type="module">
        import { requestForToken } from './js/firebase-messaging.js';

        document.addEventListener('DOMContentLoaded', function() {
            // Запрашиваем токен при загрузке админ-панели
            setTimeout(() => {
                requestForToken();
            }, 2000);
        });
    </script>
    --}}
    <x-slot:title>
        {{ __('newsletters::app.admin.mailing-lists.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('newsletters::app.admin.mailing-lists.title') }}
        </h1>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.mailing-lists.create') }}" class="primary-button">
                {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.mailing-lists.title_sklon') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.id') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.mailing-lists.message-text') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.mailing-lists.active') }}
                        </th>
{{--                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">--}}
{{--                            {{ __('newsletters::app.admin.mailing-lists.start-at') }}--}}
{{--                        </th>--}}


                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.numbers_count') }}
                        </th>
                        {{--numbers_delivered column--}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.sent_count') }}
                        </th>
                        {{--incoming_message column--}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.incoming_count') }}
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
                    @forelse($mailingLists as $mailingList)
                        <tr data-mailing-list-id="{{ $mailingList->id }}" class="{{$mailingList->incoming_messages_count>0?'bg-green-100':''}}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $mailingList->id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                <a href="{{ route('admin.newsletters.mailing-lists.edit', $mailingList->id) }}">
                                    {{ Str::limit($mailingList->message_text, 50) }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $mailingList->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $mailingList->active ? __('newsletters::app.admin.mailing-lists.is-active') : __('newsletters::app.admin.mailing-lists.not-active') }}
                                </span>
                            </td>
{{--                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">--}}
{{--                                @php--}}
{{--                                //dd($mailingList->start_at);--}}
{{--                                @endphp--}}
{{--                                {{ ($mailingList->start_at && $mailingList->start_at->year > 1) ? $mailingList->start_at->format('Y-m-d H:i') : '-' }}--}}
{{--                            </td>--}}


                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $mailingList->customerNumbers->count() }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" data-field="sent_count">
                                {{ $mailingList->numbers_delivered }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" data-field="incoming_count">
                                {{ $mailingList->incoming_messages_count ?: '-'}}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $mailingList->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.newsletters.mailing-lists.edit', $mailingList->id) }}"
                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="{{ __('newsletters::app.common.actions.edit') }}">
                                        <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-edit"></span>
                                    </a>
                                    <button onclick="deleteMailingList({{ $mailingList->id }})"
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
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('newsletters::app.admin.mailing-lists.no-lists') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('newsletters::app.common.messages.get_started') }}
                                    </p>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.newsletters.mailing-lists.create') }}"
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.mailing-lists.title') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Laravel Echo and Pusher -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Initialize Pusher with Reverb configuration
        const pusher = new Pusher('{{ config('broadcasting.connections.reverb.key') }}', {
            cluster: '{{ config('broadcasting.connections.reverb.options.cluster', 'mt1') }}',
            wsHost: '{{ config('broadcasting.connections.reverb.options.host', 'localhost') }}',
            wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
            wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
            forceTLS: {{ config('broadcasting.connections.reverb.options.useTLS', false) ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
        });

        // Subscribe to mailing lists stats channel
        const channel = pusher.subscribe('mailing-lists-stats');

        // Listen for stats updates
        channel.bind('stats-updated', function(data) {
            console.log('Stats updated:', data);

            const mailingListId = data.mailing_list_id;
            const stats = data.stats;

            // Find the row and update the cells
            const row = document.querySelector(`tr[data-mailing-list-id="${mailingListId}"]`);
            if (row) {
                // Update sent_count column (7th column)
                const sentCountCell = row.querySelector('[data-field="sent_count"]');
                if (sentCountCell) {
                    sentCountCell.textContent = stats.sent_count;
                }

                // Update incoming_count column (8th column)
                const incomingCountCell = row.querySelector('[data-field="incoming_count"]');
                if (incomingCountCell) {
                    incomingCountCell.textContent = stats.incoming_count || '-';
                }

                // Add visual feedback
                row.style.backgroundColor = '#f0f9ff';
                row.style.transition = 'background-color 0.3s ease';

                setTimeout(() => {
                    row.style.backgroundColor = '';
                }, 2000);

                // Show notification
                showNotification(`Stats updated for mailing list #${mailingListId}`);
            }
        });

        // Show notification function
        function showNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            notification.textContent = message;

            document.body.appendChild(notification);

            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Handle connection errors
        pusher.connection.bind('error', function(err) {
            console.error('Pusher connection error:', err);
        });

        // Handle connection state changes
        pusher.connection.bind('state_change', function(states) {
            console.log('Pusher connection state:', states.current);
        });
    </script>

    <script>
        function deleteMailingList(id) {
            if (confirm('{{ __("newsletters::app.admin.mailing-lists.delete-confirm") }}')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                
                if (!csrfToken) {
                    console.error('CSRF token not found!');
                    alert('Security token not found. Please refresh the page and try again.');
                    return;
                }

                fetch('{{ route("admin.newsletters.mailing-lists.destroy", ":id") }}'.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
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
                    alert('{{ __("newsletters::app.admin.mailing-lists.delete-failed") }}: ' + error.message);
                });
            }
        }
    </script>
</x-admin::layouts>
