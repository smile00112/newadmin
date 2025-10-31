<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.sidebar.messages') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('newsletters::app.sidebar.messages') }}
        </h1>
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
                            {{ __('newsletters::app.admin.customer-numbers.phone-number') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.customer-numbers.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.customer-numbers.mailing-list') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.messages.status') }}
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
                    @forelse($messages as $message)
                        <tr data-message-id="{{ $message->id }}"
                            class="{{ $message->incoming_message ? 'bg-green-100 dark:bg-green-900 dark:bg-opacity-20' : '' }} hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $message->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                {{ $message->phone_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $message->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @if($message->mailingList)
                                    <a href="{{ route('admin.newsletters.mailing-lists.edit', $message->mailingList->id) }}"
                                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                        {{ Str::limit($message->mailingList->message_text ?? 'N/A', 30) }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    @if($message->incoming_message)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                            <svg class="w-3 h-3 mr-1 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                            </svg>
                                            {{ __('newsletters::app.admin.messages.new-message') }}
                                        </span>
                                    @endif
                                    @if($message->delivered)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            <svg class="w-3 h-3 mr-1 inline-block h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('newsletters::app.admin.customer-numbers.delivered') }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            <svg class="w-3 h-3 mr-1 inline-block h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('newsletters::app.admin.customer-numbers.not-delivered') }}
                                        </span>
                                    @endif
                                    @if($message->viewed)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            <svg class="w-3 h-3 mr-1 inline-block h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('newsletters::app.admin.customer-numbers.viewed') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $message->created_at ? $message->created_at->format('d.m.Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button"
                                    onclick="openCustomerEditModal({{ $message->id }}, '{{ $message->phone_number }}', '{{ $message->name ?? '' }}', {{ $message->delivered ? 'true' : 'false' }}, {{ $message->viewed ? 'true' : 'false' }})"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                    title="{{ __('newsletters::app.admin.customer-numbers.edit-button-caption') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-edit"></span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ __('newsletters::app.admin.messages.no-messages') }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('newsletters::app.admin.messages.no-messages-description') }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($messages->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $messages->links() }}
            </div>
        @endif
    </div>

    <!-- Customer Edit Modal -->
    <div id="customerEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 9999;" onclick="closeCustomerEditModal()">
        <div class="modal-content relative top-20 mx-auto p-4 sm:p-6 border w-80 max-w-md shadow-xl rounded-lg bg-white dark:bg-gray-800" style="z-index: 10000;" onclick="event.stopPropagation()">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('newsletters::app.admin.customer-numbers.edit-customer') }}
                    </h3>
                    <button onclick="closeCustomerEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="customerEditForm">
                    <input type="hidden" id="editCustomerId" name="customer_id">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.phone-number') }}
                        </label>
                        <input type="text" id="editPhoneNumber" name="phone_number"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.name') }}
                        </label>
                        <input type="text" id="editCustomerName" name="name"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.delivered') }}
                        </label>
                        <select id="editDelivered" name="delivered"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="0">{{ __('newsletters::app.admin.customer-numbers.not-delivered') }}</option>
                            <option value="1">{{ __('newsletters::app.admin.customer-numbers.delivered') }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.viewed') }}
                        </label>
                        <select id="editViewed" name="viewed"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="0">{{ __('newsletters::app.admin.customer-numbers.not-viewed') }}</option>
                            <option value="1">{{ __('newsletters::app.admin.customer-numbers.viewed') }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.chat-with-client') }}
                        </label>
                        <textarea id="editChatHistory" name="chat_history" rows="8"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="{{ __('newsletters::app.admin.customer-numbers.loading-chat') }}"
                            readonly></textarea>
                    </div>

                    <!-- Reply Message Section -->
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.reply-message') }}
                        </label>
                        <div class="flex gap-2">
                            <textarea id="replyMessageText" rows="3"
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white"
                                placeholder="{{ __('newsletters::app.admin.customer-numbers.type-your-message') }}"></textarea>
                            <button type="button" onclick="sendReplyMessage()"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 self-end">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                {{ __('newsletters::app.common.actions.send') }}
                            </button>
                        </div>
                        <div id="replyMessageStatus" class="mt-2 text-sm hidden"></div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCustomerEditModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            {{ __('newsletters::app.common.actions.cancel') }}
                        </button>
                        <button type="button" onclick="saveCustomerChanges()"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        #customerEditModal {
            z-index: 9999 !important;
        }
        #customerEditModal .modal-content {
            z-index: 10000 !important;
        }
    </style>

    <!-- Include Pusher for real-time updates -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // Initialize Pusher with Reverb configuration
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
        
        const pusher = new Pusher('{{ config('broadcasting.connections.reverb.key') }}', {
            cluster: '{{ config('broadcasting.connections.reverb.options.cluster', 'mt1') }}',
            wsHost: finalWsHost,
            wsPort: wsPort,
            wssPort: wssPort,
            forceTLS: useTLS,
            enabledTransports: ['ws', 'wss'],
        });

        // Subscribe to customer numbers channel for real-time updates
        const channel = pusher.subscribe('customer-numbers');

        // Listen for new incoming messages
        channel.bind('message-read', function(data) {
            console.log('Message read:', data);

            const messageId = data.customer_number_id;
            const row = document.querySelector(`tr[data-message-id="${messageId}"]`);

            if (row) {
                // Remove green highlighting
                row.classList.remove('bg-green-100', 'dark:bg-green-900', 'dark:bg-opacity-20');

                // Find and remove the "new message" badge
                const statusCell = row.querySelector('td:nth-child(5)'); // Status column is 5th
                if (statusCell) {
                    const newMessageBadge = statusCell.querySelector('.bg-yellow-100');
                    if (newMessageBadge) {
                        newMessageBadge.remove();
                    }
                }
            }
        });

        // Handle connection errors
        pusher.connection.bind('error', function(err) {
            console.error('Pusher connection error:', err);
        });

        // Handle connection state changes
        pusher.connection.bind('state_change', function(states) {
            console.log('Pusher connection state:', states.current);
        });

        // Customer Edit Modal Functions
        function openCustomerEditModal(id, phoneNumber, name, delivered, viewed) {
            const editCustomerId = document.getElementById('editCustomerId');
            const editPhoneNumber = document.getElementById('editPhoneNumber');
            const editCustomerName = document.getElementById('editCustomerName');
            const editDelivered = document.getElementById('editDelivered');
            const editViewed = document.getElementById('editViewed');
            const chatTextarea = document.getElementById('editChatHistory');
            const modal = document.getElementById('customerEditModal');

            if (!editCustomerId || !editPhoneNumber || !editCustomerName || !editDelivered || !editViewed || !chatTextarea || !modal) {
                console.error('Modal elements not found');
                return;
            }

            editCustomerId.value = id;
            editPhoneNumber.value = phoneNumber;
            editCustomerName.value = name;
            editDelivered.value = delivered ? '1' : '0';
            editViewed.value = viewed ? '1' : '0';

            // Clear and set loading placeholder for chat history
            chatTextarea.value = '';
            chatTextarea.placeholder = '{{ __("newsletters::app.admin.customer-numbers.loading-chat") }}';

            modal.classList.remove('hidden');

            // Load chat history via API
            loadChatHistory(id, phoneNumber);
        }

        function closeCustomerEditModal() {
            const modal = document.getElementById('customerEditModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Load chat history via API
        function loadChatHistory(customerId, phoneNumber) {
            const chatTextarea = document.getElementById('editChatHistory');

            if (!chatTextarea) {
                console.error('Chat textarea not found');
                return;
            }

            chatTextarea.value = '';
            chatTextarea.placeholder = '{{ __("newsletters::app.admin.customer-numbers.loading-chat") }}';

            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '{{ csrf_token() }}';

            fetch('{{ route("admin.newsletters.customer-numbers.chat-history") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    customer_id: customerId,
                    phone_number: phoneNumber
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    chatTextarea.value = data.chat_history || '{{ __("newsletters::app.admin.customer-numbers.no-chat-history") }}';
                    chatTextarea.placeholder = '{{ __("newsletters::app.admin.customer-numbers.chat-with-client") }}';
                } else {
                    chatTextarea.value = data.message || '{{ __("newsletters::app.admin.customer-numbers.chat-history-error") }}';
                }
            })
            .catch(error => {
                console.error('Error loading chat history:', error);
                chatTextarea.value = '{{ __("newsletters::app.admin.customer-numbers.chat-history-error") }}\n\n' +
                    '{{ __("newsletters::app.admin.customer-numbers.chat-history-unavailable") }}';
            });
        }

        function saveCustomerChanges() {
            const customerId = document.getElementById('editCustomerId').value;
            const phoneNumber = document.getElementById('editPhoneNumber').value;
            const name = document.getElementById('editCustomerName').value;
            const delivered = document.getElementById('editDelivered').value;
            const viewed = document.getElementById('editViewed').value;

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                alert('Security token not found');
                return;
            }

            // Make API request to update customer
            fetch('{{ route("admin.newsletters.customer-numbers.update", ":id") }}'.replace(':id', customerId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                },
                body: JSON.stringify({
                    phone_number: phoneNumber,
                    name: name,
                    delivered: delivered,
                    viewed: viewed
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                alert('{{ __("newsletters::app.admin.customer-numbers.update-success") }}');
                closeCustomerEditModal();
                location.reload(); // Reload to show updated data
            })
            .catch(error => {
                console.error('Error updating customer:', error);
                alert('{{ __("newsletters::app.admin.customer-numbers.update-failed") }}: ' + error.message);
            });
        }

        function sendReplyMessage() {
            const customerId = document.getElementById('editCustomerId').value;
            const messageText = document.getElementById('replyMessageText').value.trim();
            const statusDiv = document.getElementById('replyMessageStatus');

            if (!messageText) {
                showReplyStatus('{{ __("newsletters::app.admin.customer-numbers.message-empty-error") }}', 'error');
                return;
            }

            const sendButton = event.target;
            const originalButtonContent = sendButton.innerHTML;
            sendButton.disabled = true;
            sendButton.innerHTML = '<svg class="w-5 h-5 inline-block animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __("newsletters::app.common.actions.sending") }}...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                showReplyStatus('Security token not found', 'error');
                sendButton.disabled = false;
                sendButton.innerHTML = originalButtonContent;
                return;
            }

            fetch('{{ route("admin.newsletters.customer-numbers.send-reply") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                },
                body: JSON.stringify({
                    customer_number_id: customerId,
                    message: messageText
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showReplyStatus(data.message, 'success');
                    document.getElementById('replyMessageText').value = '';

                    const phoneNumber = document.getElementById('editPhoneNumber').value;
                    loadChatHistory(customerId, phoneNumber);
                } else {
                    showReplyStatus(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                showReplyStatus('{{ __("newsletters::app.admin.customer-numbers.message-sent-failed") }}: ' + error.message, 'error');
            })
            .finally(() => {
                sendButton.disabled = false;
                sendButton.innerHTML = originalButtonContent;
            });
        }

        function showReplyStatus(message, type) {
            const statusDiv = document.getElementById('replyMessageStatus');
            statusDiv.textContent = message;
            statusDiv.className = 'mt-2 text-sm ';

            if (type === 'success') {
                statusDiv.className += 'text-green-600 dark:text-green-400';
            } else {
                statusDiv.className += 'text-red-600 dark:text-red-400';
            }

            statusDiv.classList.remove('hidden');

            setTimeout(() => {
                statusDiv.classList.add('hidden');
            }, 5000);
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCustomerEditModal();
            }
        });
    </script>
</x-admin::layouts>

