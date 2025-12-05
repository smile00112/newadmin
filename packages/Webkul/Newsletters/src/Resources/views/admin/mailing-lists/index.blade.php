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

    @if(!$hasBalance)
        <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ __('newsletters::app.admin.account.insufficient-balance-warning') }}
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>
                            {{ __('newsletters::app.admin.account.insufficient-balance') }}
                            <a href="{{ route('admin.newsletters.account.index') }}" class="font-medium underline">
                                {{ __('newsletters::app.admin.account.topup-title') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.status') }}
                        </th>
{{--                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">--}}
{{--                            {{ __('newsletters::app.admin.mailing-lists.start-at') }}--}}
{{--                        </th>--}}


{{--                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">--}}
{{--                            {{ __('newsletters::app.common.fields.numbers_count') }}--}}
{{--                        </th>--}}
                        {{--Progress column--}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.mailing-lists.progress') }}
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" data-field="status">
                                {{ __('newsletters::app.admin.mailing-lists.' . $mailingList->status) }}
                            </td>
{{--                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">--}}
{{--                                @php--}}
{{--                                //dd($mailingList->start_at);--}}
{{--                                @endphp--}}
{{--                                {{ ($mailingList->start_at && $mailingList->start_at->year > 1) ? $mailingList->start_at->format('Y-m-d H:i') : '-' }}--}}
{{--                            </td>--}}


{{--                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">--}}
{{--                                {{ $mailingList->customerNumbers->count() }}--}}
{{--                            </td>--}}

                            <td class="px-6 py-4 text-sm" data-field="progress">
                                @php
                                    $totalNumbers = $mailingList->customerNumbers->count();
                                    $sentNumbers = $mailingList->numbers_delivered;
                                    $progressPercentage = $totalNumbers > 0 ? round(($sentNumbers / $totalNumbers) * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 flex-shrink-0 bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 min-w-[100px]">
                                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                             style="width: {{ $progressPercentage }}%"
                                             data-progress="{{ $progressPercentage }}"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 dark:text-gray-400 min-w-[80px]" data-field="progress-text">
                                        <span data-field="sent_count">{{ $sentNumbers }}</span> / {{ $totalNumbers }} ({{ $progressPercentage }}%)
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" data-field="incoming_count">
                                {{ $mailingList->incoming_messages_count ?: '-'}}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $mailingList->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if(!$mailingList->active)
                                        @if($hasBalance)
                                            <button onclick="startMailing({{ $mailingList->id }})"
                                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                                    title="{{ __('newsletters::app.admin.mailing-lists.start-mailing') }}"
                                                    id="start-btn-{{ $mailingList->id }}">
                                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-arrow-left bg-[#B5DCB4]"></span>
                                            </button>
                                        @else
                                            <button disabled
                                                    class="text-gray-400 dark:text-gray-600 cursor-not-allowed opacity-50"
                                                    title="{{ __('newsletters::app.admin.account.insufficient-balance-warning') }}"
                                                    id="start-btn-{{ $mailingList->id }}">
                                                <span class="rounded-md p-1.5 text-2xl icon-arrow-left bg-gray-300 dark:bg-gray-700"></span>
                                            </button>
                                        @endif
                                    @else
                                        <button onclick="pauseMailing({{ $mailingList->id }})"
                                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                                title="{{ __('newsletters::app.admin.mailing-lists.pause-mailing') }}"
                                                id="pause-btn-{{ $mailingList->id }}">
                                            <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-pause bg-[#FFD700]"></span>
                                        </button>
                                    @endif
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
        // Initialize Pusher with Reverb configuration only if Reverb is configured
        const reverbKey = '{{ config('broadcasting.connections.reverb.key') }}';

        if (reverbKey && reverbKey !== '') {
            try {
                // For local development, always use localhost for WebSocket connections
                const wsHost = '{{ config('broadcasting.connections.reverb.options.host', 'localhost') }}';
                const isLocal = window.location.hostname === 'localhost' ||
                               window.location.hostname === '127.0.0.1' ||
                               window.location.hostname.includes('.test') ||
                               window.location.hostname.includes('.local');

                // Для локальной разработки используем localhost, для продакшена - текущий домен
                const finalWsHost = isLocal ? 'localhost' : (wsHost || window.location.hostname);

                // Для продакшена в Coolify порты должны быть стандартными (80/443), не 8080
                // Traefik проксирует WebSocket на внутренний порт 8080 автоматически
                const wsPort = isLocal ? {{ config('broadcasting.connections.reverb.options.port', 8080) }} : 80;
                const wssPort = isLocal ? {{ config('broadcasting.connections.reverb.options.port', 8080) }} : 443;
                // Для локальной разработки всегда используем ws:// (forceTLS: false)
                // Для продакшена используем настройку из конфига
                const useTLS = isLocal ? false : ({{ config('broadcasting.connections.reverb.options.useTLS', false) ? 'true' : 'false' }});

                const pusher = new Pusher(reverbKey, {
                    cluster: '{{ config('broadcasting.connections.reverb.options.cluster', 'mt1') }}',
                    wsHost: finalWsHost,
                    wsPort: wsPort,
                    wssPort: wssPort,
                    forceTLS: useTLS,
                    enabledTransports: ['ws', 'wss'],
                    disableStats: true,
                });

                // Handle connection errors silently
                pusher.connection.bind('error', function(err) {
                    // Suppress error messages if connection fails (Reverb might not be running)
                    // Only log if in debug mode
                    if (window.location.hostname === 'localhost' || window.location.hostname.includes('.test')) {
                        console.warn('WebSocket connection unavailable. Real-time updates disabled.');
                    }
                });

                // Handle connection state changes
                pusher.connection.bind('connected', function() {
                    // Subscribe only after successful connection
                    try {
                        // Subscribe to general stats channel
                        const statsChannel = pusher.subscribe('mailing-lists-stats');

                        // Listen for stats updates (from Observer)
                        statsChannel.bind('stats-updated', function(data) {
                            console.log('Stats updated:', data);

                            const mailingListId = data.mailing_list_id;
                            const stats = data.stats;

                            // Find the row and update the cells
                            const row = document.querySelector(`tr[data-mailing-list-id="${mailingListId}"]`);
                            if (row) {
                                // Update progress bar
                                updateProgressBar(row, stats.sent_count, stats.total_count);

                                // Update incoming_count column
                                const incomingCountCell = row.querySelector('[data-field="incoming_count"]');
                                if (incomingCountCell) {
                                    incomingCountCell.textContent = stats.incoming_count || '-';
                                }

                                // Update status column heuristically by stats
                                const statusCell = row.querySelector('[data-field="status"]');
                                if (statusCell && stats && typeof stats.sent_count !== 'undefined' && typeof stats.total_count !== 'undefined') {
                                    if (stats.total_count > 0 && stats.sent_count >= stats.total_count) {
                                        statusCell.textContent = 'completed';
                                    } else if (stats.sent_count > 0) {
                                        statusCell.textContent = 'sending';
                                    }
                                }

                                // Add visual feedback
                                row.style.backgroundColor = '#f0f9ff';
                                row.style.transition = 'background-color 0.3s ease';

                                setTimeout(() => {
                                    row.style.backgroundColor = '';
                                }, 2000);

                                // Show notification
                                showNotification(`Отправлено ${stats.sent_count} из ${stats.total_count} сообщений`);
                            }
                        });

                        // Subscribe to individual mailing list channels for message.sent events
                        // Get all mailing list IDs from the page
                        const mailingListRows = document.querySelectorAll('tr[data-mailing-list-id]');
                        mailingListRows.forEach(function(row) {
                            const mailingListId = row.getAttribute('data-mailing-list-id');
                            if (mailingListId) {
                                try {
                                    const mailingListChannel = pusher.subscribe('mailing-list.' + mailingListId);

                                    // Listen for individual message sent events
                                    mailingListChannel.bind('message.sent', function(data) {
                                        console.log('Message sent event:', data);

                                        // Increment sent count immediately for faster UI update
                                        const row = document.querySelector(`tr[data-mailing-list-id="${data.mailing_list_id}"]`);
                                        if (row) {
                                            const progressCell = row.querySelector('[data-field="progress"]');
                                            if (progressCell) {
                                                const sentCountSpan = progressCell.querySelector('[data-field="sent_count"]');
                                                const progressText = progressCell.querySelector('[data-field="progress-text"]');
                                                const progressBar = progressCell.querySelector('[data-progress]');

                                                if (sentCountSpan && progressText && progressBar) {
                                                    // Get current values
                                                    const currentSent = parseInt(sentCountSpan.textContent) || 0;
                                                    const totalText = progressText.textContent.match(/\/(\d+)/);
                                                    const total = totalText ? parseInt(totalText[1]) : 0;

                                                    // Increment sent count
                                                    const newSent = currentSent + 1;
                                                    const percentage = total > 0 ? Math.round((newSent / total) * 100) : 0;

                                                    // Update UI immediately
                                                    progressBar.style.width = percentage + '%';
                                                    progressBar.setAttribute('data-progress', percentage);
                                                    sentCountSpan.textContent = newSent;
                                                    progressText.innerHTML = `<span data-field="sent_count">${newSent}</span> / ${total} (${percentage}%)`;

                                                    // Add visual feedback
                                                    row.style.backgroundColor = '#f0f9ff';
                                                    row.style.transition = 'background-color 0.3s ease';

                                                    setTimeout(() => {
                                                        row.style.backgroundColor = '';
                                                    }, 1000);
                                                }
                                            }
                                        }
                                    });
                                } catch (e) {
                                    if (window.location.hostname === 'localhost' || window.location.hostname.includes('.test')) {
                                        console.warn('Failed to subscribe to mailing list channel:', mailingListId, e);
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        // Subscription failed, ignore silently
                        if (window.location.hostname === 'localhost' || window.location.hostname.includes('.test')) {
                            console.warn('Failed to subscribe to WebSocket channel:', e);
                        }
                    }
                });

            } catch (e) {
                // Pusher initialization failed, ignore silently
                if (window.location.hostname === 'localhost' || window.location.hostname.includes('.test')) {
                    console.warn('WebSocket initialization failed. Real-time updates disabled.');
                }
            }
        }

        // Update progress bar function
        function updateProgressBar(row, sentCount, totalCount) {
            const progressCell = row.querySelector('[data-field="progress"]');
            if (!progressCell) return;

            const progressBar = progressCell.querySelector('[data-progress]');
            const progressText = progressCell.querySelector('[data-field="progress-text"]');
            const sentCountSpan = progressCell.querySelector('[data-field="sent_count"]');

            if (progressBar && progressText && sentCountSpan) {
                const percentage = totalCount > 0 ? Math.round((sentCount / totalCount) * 100) : 0;

                progressBar.style.width = percentage + '%';
                progressBar.setAttribute('data-progress', percentage);
                sentCountSpan.textContent = sentCount;
                progressText.innerHTML = `<span data-field="sent_count">${sentCount}</span> / ${totalCount} (${percentage}%)`;
            }
        }

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
    </script>

    <script>
        function startMailing(id) {
            if (confirm('{{ __("newsletters::app.admin.mailing-lists.start-confirm") }}')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                const startButton = document.getElementById('start-btn-' + id);

                if (!csrfToken) {
                    console.error('CSRF token not found!');
                    alert('Security token not found. Please refresh the page and try again.');
                    return;
                }

                // Disable button and show loading state
                if (startButton) {
                    startButton.disabled = true;
                    startButton.style.opacity = '0.5';
                }

                fetch('{{ route("admin.newsletters.mailing-lists.start", ":id") }}'.replace(':id', id), {
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
                        showNotification(data.message);

                        // Hide start button after successful start
                        if (startButton) {
                            startButton.style.display = 'none';
                        }

                        // Show pause button
                        const row = document.querySelector(`tr[data-mailing-list-id="${id}"]`);
                        if (row) {
                            const actionsCell = row.querySelector('td:last-child .flex');
                            if (actionsCell) {
                                const pauseButton = document.createElement('button');
                                pauseButton.onclick = () => pauseMailing(id);
                                pauseButton.className = 'text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300';
                                pauseButton.title = '{{ __("newsletters::app.admin.mailing-lists.pause-mailing") }}';
                                pauseButton.id = 'pause-btn-' + id;
                                pauseButton.innerHTML = '<span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-pause bg-[#FFD700]"></span>';
                                actionsCell.insertBefore(pauseButton, actionsCell.firstChild);
                            }
                        }

                        // Update active status badge
                        if (row) {
                            const statusBadge = row.querySelector('td:nth-child(3) span');
                            if (statusBadge) {
                                statusBadge.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
                                statusBadge.textContent = '{{ __("newsletters::app.admin.mailing-lists.is-active") }}';
                            }
                        }
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
                    alert(error.message || '{{ __("newsletters::app.admin.mailing-lists.mailing-start-failed") }}');
                    if (startButton) {
                        startButton.disabled = false;
                        startButton.style.opacity = '1';
                    }
                });
            }
        }

        function pauseMailing(id) {
            if (confirm('{{ __("newsletters::app.admin.mailing-lists.pause-confirm") }}')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                const pauseButton = document.getElementById('pause-btn-' + id);

                if (!csrfToken) {
                    console.error('CSRF token not found!');
                    alert('Security token not found. Please refresh the page and try again.');
                    return;
                }

                // Disable button and show loading state
                if (pauseButton) {
                    pauseButton.disabled = true;
                    pauseButton.style.opacity = '0.5';
                }

                fetch('{{ route("admin.newsletters.mailing-lists.pause", ":id") }}'.replace(':id', id), {
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
                        showNotification(data.message);

                        // Hide pause button after successful pause
                        if (pauseButton) {
                            pauseButton.style.display = 'none';
                        }

                        // Show start button
                        const row = document.querySelector(`tr[data-mailing-list-id="${id}"]`);
                        if (row) {
                            const actionsCell = row.querySelector('td:last-child .flex');
                            if (actionsCell) {
                                const startButton = document.createElement('button');
                                startButton.onclick = () => startMailing(id);
                                startButton.className = 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300';
                                startButton.title = '{{ __("newsletters::app.admin.mailing-lists.start-mailing") }}';
                                startButton.id = 'start-btn-' + id;
                                startButton.innerHTML = '<span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-arrow-left bg-[#B5DCB4]"></span>';
                                actionsCell.insertBefore(startButton, actionsCell.firstChild);
                            }
                        }

                        // Update active status badge
                        if (row) {
                            const statusBadge = row.querySelector('td:nth-child(3) span');
                            if (statusBadge) {
                                statusBadge.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
                                statusBadge.textContent = '{{ __("newsletters::app.admin.mailing-lists.not-active") }}';
                            }
                        }
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
                    alert(error.message || '{{ __("newsletters::app.admin.mailing-lists.mailing-pause-failed") }}');
                    if (pauseButton) {
                        pauseButton.disabled = false;
                        pauseButton.style.opacity = '1';
                    }
                });
            }
        }

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
