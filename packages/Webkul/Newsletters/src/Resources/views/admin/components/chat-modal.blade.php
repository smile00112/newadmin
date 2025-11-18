@php
    $modalId = $modalId ?? 'newslettersChatModal';
@endphp

<div
    id="{{ $modalId }}"
    data-newsletters-chat-modal
    class="fixed inset-0 z-[10002] hidden"
    aria-hidden="true"
>
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" data-chat-close></div>

    <div class="relative z-10 mx-auto mt-10 mb-10 w-full max-w-3xl px-4 sm:px-6 lg:max-w-4xl lg:mt-20" data-chat-modal-content>
        <div class="max-h-[calc(100vh-5rem)] lg:max-h-[calc(100vh-8rem)] overflow-y-auto rounded-xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ __('newsletters::app.admin.customer-numbers.chat-with-client') }}
                    </p>

                    <div class="mt-1 text-sm text-gray-700 dark:text-gray-200">
                        <div class="font-semibold text-lg text-gray-900 dark:text-white" data-chat-customer-name>—</div>
                        <div class="mt-1 flex flex-wrap gap-3 text-xs">
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.phone-number') }}:</span>
                                <span data-chat-phone-number>—</span>
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.whatsapp-instances.instance-phone') }}:</span>
                                <span data-chat-instance-phone>—</span>
                            </span>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                    data-chat-close
                    aria-label="{{ __('newsletters::app.common.actions.close') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4 px-5 py-4">
                <input type="hidden" data-chat-customer-id>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('newsletters::app.admin.customer-numbers.chat-with-client') }}
                    </label>

                    <textarea
                        rows="10"
                        data-chat-history
                        readonly
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-sm text-gray-800 shadow-sm focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                        placeholder="{{ __('newsletters::app.admin.customer-numbers.loading-chat') }}"
                    ></textarea>
                </div>

                <div data-chat-reply-section class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('newsletters::app.admin.customer-numbers.reply-message') }}
                    </label>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <textarea
                            rows="3"
                            data-chat-reply-input
                            class="flex-1 rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="{{ __('newsletters::app.admin.customer-numbers.type-your-message') }}"
                        ></textarea>

                        <button
                            type="button"
                            data-chat-send
                            class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            {{ __('newsletters::app.common.actions.send') }}
                        </button>
                    </div>

                    <div data-chat-status class="mt-2 hidden text-sm"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            function initChatModal() {
                const modal = document.querySelector('[data-newsletters-chat-modal]');

                if (!modal) {
                    console.warn('NewslettersChat: modal element not found');
                    window.NewslettersChat = window.NewslettersChat || {
                        open: () => {},
                        close: () => {},
                        sendReply: () => {},
                    };

                    return;
                }

            const elements = {
                modal,
                customerId: modal.querySelector('[data-chat-customer-id]'),
                customerName: modal.querySelector('[data-chat-customer-name]'),
                phoneNumber: modal.querySelector('[data-chat-phone-number]'),
                instancePhone: modal.querySelector('[data-chat-instance-phone]'),
                history: modal.querySelector('[data-chat-history]'),
                replySection: modal.querySelector('[data-chat-reply-section]'),
                replyInput: modal.querySelector('[data-chat-reply-input]'),
                status: modal.querySelector('[data-chat-status]'),
                sendButton: modal.querySelector('[data-chat-send]'),
            };

            // Проверка наличия всех необходимых элементов
            const requiredElements = ['customerId', 'customerName', 'phoneNumber', 'history', 'replyInput', 'sendButton'];
            const missingElements = requiredElements.filter(key => !elements[key]);
            if (missingElements.length > 0) {
                console.error('NewslettersChat: missing required elements:', missingElements);
            } else {
                console.log('NewslettersChat: all elements found, modal initialized');
            }

            const config = {
                routes: {
                    history: '{{ route('admin.newsletters.customer-numbers.chat-history') }}',
                    reply: '{{ route('admin.newsletters.customer-numbers.send-reply') }}',
                },
                i18n: {
                    loadingChat: @json(__('newsletters::app.admin.customer-numbers.loading-chat')),
                    chatPlaceholder: @json(__('newsletters::app.admin.customer-numbers.chat-with-client')),
                    chatError: @json(__('newsletters::app.admin.customer-numbers.chat-history-error')),
                    chatUnavailable: @json(__('newsletters::app.admin.customer-numbers.chat-history-unavailable')),
                    noChat: @json(__('newsletters::app.admin.customer-numbers.no-chat-history')),
                    messageEmpty: @json(__('newsletters::app.admin.customer-numbers.message-empty-error')),
                    messageFailed: @json(__('newsletters::app.admin.customer-numbers.message-sent-failed')),
                    sending: @json(__('newsletters::app.common.actions.sending')),
                    send: @json(__('newsletters::app.common.actions.send')),
                    successDefault: @json(__('newsletters::app.admin.customer-numbers.message-sent-success') ?? __('newsletters::app.common.messages.success')),
                    nameFallback: '—',
                    instanceFallback: '—',
                },
                errorMarkers: [
                    @json(__('newsletters::app.admin.customer-numbers.no-whatsapp-instance')),
                    @json(__('newsletters::app.admin.customer-numbers.chat-history-unavailable')),
                    'недоступна',
                    'не найдена',
                ],
            };

            const state = {
                customerId: null,
                phoneNumber: '',
                name: '',
                instancePhone: '',
            };

            function getCsrfToken() {
                const tokenElement = document.querySelector('meta[name="csrf-token"]');
                return tokenElement ? tokenElement.getAttribute('content') : '{{ csrf_token() }}';
            }

            function open(payload = {}) {
                console.log('NewslettersChat: open called with payload', payload);
                
                state.customerId = payload.id || payload.customer_number_id || null;
                state.phoneNumber = payload.phone_number || payload.phoneNumber || '';
                state.name = payload.name || '';
                state.instancePhone = payload.instance_phone || payload.instancePhone || '';

                if (!state.customerId) {
                    console.warn('NewslettersChat: customer id is required to open chat modal.', payload);
                    return;
                }

                if (!elements.customerId) {
                    console.error('NewslettersChat: customerId element not found');
                    return;
                }

                elements.customerId.value = state.customerId;
                elements.customerName.textContent = state.name || config.i18n.nameFallback;
                elements.phoneNumber.textContent = state.phoneNumber || '—';
                elements.instancePhone.textContent = state.instancePhone || config.i18n.instanceFallback;

                elements.history.value = '';
                elements.history.placeholder = config.i18n.loadingChat;
                elements.replyInput.value = '';
                elements.status.classList.add('hidden');
                elements.status.textContent = '';
                toggleReplySection(true);

                console.log('NewslettersChat: showing modal');
                elements.modal.classList.remove('hidden');

                loadHistory();
            }

            function close() {
                elements.modal.classList.add('hidden');
            }

            function toggleReplySection(visible) {
                if (!elements.replySection) {
                    return;
                }

                elements.replySection.classList.toggle('hidden', !visible);
            }

            function loadHistory() {
                if (!state.customerId) {
                    return;
                }

                elements.history.value = '';
                elements.history.placeholder = config.i18n.loadingChat;

                const csrfToken = getCsrfToken();

                fetch(config.routes.history, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        customer_id: state.customerId,
                        phone_number: state.phoneNumber,
                    }),
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        return response.json();
                    })
                    .then((data) => {
                        if (data.success) {
                            const historyText = data.chat_history || config.i18n.noChat;
                            elements.history.value = historyText;
                            elements.history.placeholder = config.i18n.chatPlaceholder;

                            const shouldHideReply = config.errorMarkers.some((marker) =>
                                historyText.includes(marker)
                            );

                            toggleReplySection(!shouldHideReply);
                        } else {
                            elements.history.value = data.message || config.i18n.chatError;
                            toggleReplySection(false);
                        }
                    })
                    .catch((error) => {
                        console.error('NewslettersChat: error loading chat history', error);
                        elements.history.value = `${config.i18n.chatError}\n\n${config.i18n.chatUnavailable}`;
                        toggleReplySection(false);
                    });
            }

            function sendReply(event) {
                const messageText = elements.replyInput.value.trim();

                if (!messageText) {
                    showStatus(config.i18n.messageEmpty, 'error');
                    return;
                }

                const csrfToken = getCsrfToken();

                const button = event?.currentTarget || elements.sendButton;
                const originalContent = button.innerHTML;
                button.disabled = true;
                button.innerHTML = `
                    <svg class="mr-2 h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    ${config.i18n.sending}...
                `;

                fetch(config.routes.reply, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        customer_number_id: state.customerId,
                        message: messageText,
                    }),
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        return response.json();
                    })
                    .then((data) => {
                        if (data.success) {
                            showStatus(data.message || config.i18n.successDefault, 'success');
                            elements.replyInput.value = '';
                            loadHistory();

                            setTimeout(() => loadHistory(), 1000);
                        } else {
                            showStatus(data.message || config.i18n.messageFailed, 'error');
                        }
                    })
                    .catch((error) => {
                        console.error('NewslettersChat: error sending reply', error);
                        showStatus(`${config.i18n.messageFailed}: ${error.message}`, 'error');
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.innerHTML = originalContent;
                    });
            }

            function showStatus(message, type = 'success') {
                if (!elements.status) {
                    return;
                }

                elements.status.textContent = message;
                elements.status.classList.remove('hidden');
                elements.status.classList.remove('text-green-500', 'text-red-500');
                elements.status.classList.add(type === 'success' ? 'text-green-600' : 'text-red-600');

                setTimeout(() => {
                    elements.status.classList.add('hidden');
                }, 5000);
            }

            function parseChatPayload(element) {
                const payloadAttr = element.getAttribute('data-chat-payload');

                if (!payloadAttr) {
                    return {};
                }

                try {
                    return JSON.parse(payloadAttr);
                } catch (error) {
                    console.error('NewslettersChat: failed to parse chat payload', error);
                    return {};
                }
            }

            window.NewslettersChat = {
                open,
                close,
                sendReply,
                reload: loadHistory,
            };

            elements.sendButton?.addEventListener('click', sendReply);

            // Обработчик кликов для открытия модалки (делегирование событий)
            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-chat-trigger]');

                if (trigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    console.log('NewslettersChat: trigger clicked', trigger);
                    console.log('NewslettersChat: trigger attributes', {
                        'data-chat-trigger': trigger.hasAttribute('data-chat-trigger'),
                        'data-chat-payload': trigger.getAttribute('data-chat-payload'),
                    });
                    
                    const payload = parseChatPayload(trigger);
                    console.log('NewslettersChat: parsed payload', payload);

                    if (!window.NewslettersChat || typeof window.NewslettersChat.open !== 'function') {
                        console.error('NewslettersChat: open function not available');
                        return;
                    }

                    open({
                        id: payload.id || payload.customer_number_id || trigger.getAttribute('data-customer-id'),
                        phone_number: payload.phone_number || trigger.getAttribute('data-phone-number'),
                        name: payload.name || trigger.getAttribute('data-customer-name'),
                        instance_phone: payload.instance_phone || trigger.getAttribute('data-instance-phone'),
                    });

                    return;
                }

                if (event.target.closest('[data-chat-close]')) {
                    event.preventDefault();
                    close();
                }
            }, true); // Используем capture phase для более раннего перехвата

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    close();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    close();
                }
            });
            }

            // Инициализация после загрузки DOM
            function tryInit() {
                const modal = document.querySelector('[data-newsletters-chat-modal]');
                if (modal) {
                    console.log('NewslettersChat: modal found, initializing...');
                    initChatModal();
                } else {
                    console.warn('NewslettersChat: modal not found yet, retrying...');
                    setTimeout(tryInit, 100);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    console.log('NewslettersChat: DOMContentLoaded');
                    setTimeout(tryInit, 50);
                });
            } else {
                console.log('NewslettersChat: DOM already loaded');
                setTimeout(tryInit, 50);
            }
        })();
    </script>
@endpush

