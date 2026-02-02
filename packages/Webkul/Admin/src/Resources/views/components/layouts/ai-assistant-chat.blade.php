<!-- AI Assistant Chat Widget - Header Version -->

@pushOnce('scripts')
    <!-- AI Assistant Header Button Template -->
    <script type="text/x-template" id="v-ai-assistant-header-template">
        <div class="relative">
            <!-- AI Button in Header -->
            <button 
                @click="toggleChat"
                class="group flex items-center gap-2 rounded-xl px-3 py-2 transition-all duration-300 active:scale-95 border"
                :class="isOpen 
                    ? 'bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-lg shadow-violet-500/30 border-transparent' 
                    : 'bg-violet-50 text-violet-600 border-violet-200 hover:bg-violet-100 hover:border-violet-300 hover:shadow-sm dark:bg-violet-900/20 dark:text-violet-400 dark:border-violet-800 dark:hover:bg-violet-900/30'"
                title="AI Ассистент"
            >
                <svg class="w-4 h-4 transition-transform duration-300" :class="{'group-hover:rotate-12': !isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <span class="text-sm font-medium whitespace-nowrap">Твой ассистент</span>
            </button>

            <!-- Chat Window Dropdown -->
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 scale-95 -translate-y-2"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 -translate-y-2"
            >
                <div 
                    v-if="isOpen"
                    class="absolute top-14 right-0 w-[400px] h-[550px] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl shadow-gray-900/20 dark:shadow-black/40 border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden z-[10000]"
                >
                    <!-- Header -->
                    <div class="flex-shrink-0 flex items-center justify-between px-4 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm">AI Ассистент</h3>
                                <p class="text-xs text-white/70">Помогу управлять контентом</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <!-- Clear history button -->
                            <button 
                                v-if="messages.length > 0"
                                @click="clearHistory"
                                class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors"
                                title="Очистить историю"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            <!-- Close button -->
                            <button 
                                @click="closeChat"
                                class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div 
                        ref="messagesContainer"
                        class="flex-1 min-h-0 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-800/50"
                        style="max-height: calc(550px - 130px);"
                    >
                        <!-- Welcome message -->
                        <div v-if="messages.length === 0" class="text-center py-6">
                            <div class="w-14 h-14 mx-auto mb-3 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h4 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-1">Привет! 👋</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 max-w-[280px] mx-auto mb-4">
                                Я помогу управлять магазином
                            </p>
                            <div class="space-y-2">
                                <button 
                                    v-for="suggestion in suggestions"
                                    :key="suggestion"
                                    @click="sendSuggestion(suggestion)"
                                    class="block w-full px-3 py-2 text-xs text-left text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-violet-300 dark:hover:border-violet-600 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                                >
                                    @{{ suggestion }}
                                </button>
                            </div>
                        </div>

                        <!-- Messages -->
                        <template v-for="(message, index) in messages" :key="index">
                            <!-- User message -->
                            <div v-if="message.role === 'user'" class="flex justify-end">
                                <div class="max-w-[85%] px-4 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-2xl rounded-br-md shadow-sm">
                                    <p class="text-sm whitespace-pre-wrap">@{{ message.content }}</p>
                                </div>
                            </div>

                            <!-- Assistant message -->
                            <div v-else class="flex justify-start">
                                <div class="flex gap-2 max-w-[85%]">
                                    <div class="flex-shrink-0 w-7 h-7 bg-gradient-to-br from-violet-500 to-purple-600 rounded-full flex items-center justify-center shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </div>
                                    <div class="px-4 py-2.5 bg-white dark:bg-gray-800 rounded-2xl rounded-bl-md shadow-sm border border-gray-100 dark:border-gray-700">
                                        <div class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap ai-message-content" v-html="formatMessage(message.content)"></div>
                                        
                                        <!-- Actions performed -->
                                        <div v-if="message.actions && message.actions.length" class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Выполнено:</p>
                                            <div v-for="action in message.actions" :key="action.function" class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                @{{ action.result?.message || action.function }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Typing indicator -->
                        <div v-if="isLoading" class="flex justify-start">
                            <div class="flex gap-2">
                                <div class="flex-shrink-0 w-7 h-7 bg-gradient-to-br from-violet-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div class="px-4 py-3 bg-white dark:bg-gray-800 rounded-2xl rounded-bl-md shadow-sm border border-gray-100 dark:border-gray-700">
                                    <div class="flex gap-1">
                                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="flex-shrink-0 p-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                        <form @submit.prevent="sendMessage" class="flex gap-2 items-end">
                            <textarea
                                ref="inputField"
                                v-model="inputMessage"
                                :disabled="isLoading"
                                @keydown="handleKeydown"
                                @input="autoResize"
                                placeholder="Напишите сообщение..."
                                rows="1"
                                class="flex-1 px-4 py-2.5 text-sm bg-gray-100 dark:bg-gray-800 border-0 rounded-xl focus:ring-2 focus:ring-violet-500 focus:bg-white dark:focus:bg-gray-700 transition-all placeholder-gray-400 dark:placeholder-gray-500 text-gray-800 dark:text-gray-200 resize-none overflow-hidden"
                                style="min-height: 42px; max-height: 120px;"
                            ></textarea>
                            <button
                                type="submit"
                                :disabled="isLoading || !inputMessage.trim()"
                                class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-violet-600 to-purple-600 rounded-xl text-white disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-violet-500/30 transition-all"
                            >
                                <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </transition>
        </div>
    </script>

    <script type="module">
        app.component('v-ai-assistant-header', {
            template: '#v-ai-assistant-header-template',

            data() {
                return {
                    isOpen: false,
                    isLoading: false,
                    inputMessage: '',
                    messages: [],
                    maxHistoryMessages: 10,
                    suggestions: [
                        'Покажи статистику магазина',
                        'Найди товары дороже 5000 руб.',
                        'Покажи новые заказы',
                        'Найди клиентов'
                    ]
                };
            },

            mounted() {
                // Load chat history from localStorage
                this.loadHistory();
                
                // Close on click outside
                document.addEventListener('click', this.handleClickOutside);
            },

            beforeUnmount() {
                document.removeEventListener('click', this.handleClickOutside);
            },

            watch: {
                // Save messages to localStorage when they change
                messages: {
                    handler(newMessages) {
                        this.saveHistory();
                    },
                    deep: true
                }
            },

            methods: {
                loadHistory() {
                    try {
                        const saved = localStorage.getItem('ai_assistant_history');
                        if (saved) {
                            const parsed = JSON.parse(saved);
                            if (Array.isArray(parsed)) {
                                // Load last N messages
                                this.messages = parsed.slice(-this.maxHistoryMessages);
                            }
                        }
                    } catch (e) {
                        console.error('Failed to load AI chat history:', e);
                    }
                },

                saveHistory() {
                    try {
                        // Keep only last N messages
                        const toSave = this.messages.slice(-this.maxHistoryMessages);
                        localStorage.setItem('ai_assistant_history', JSON.stringify(toSave));
                    } catch (e) {
                        console.error('Failed to save AI chat history:', e);
                    }
                },

                clearHistory() {
                    this.messages = [];
                    localStorage.removeItem('ai_assistant_history');
                },

                handleClickOutside(event) {
                    if (this.isOpen && !this.$el.contains(event.target)) {
                        this.closeChat();
                    }
                },

                toggleChat() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        this.$nextTick(() => {
                            this.scrollToBottom();
                            if (this.$refs.inputField) {
                                this.$refs.inputField.focus();
                            }
                        });
                    }
                },

                closeChat() {
                    this.isOpen = false;
                },

                handleKeydown(event) {
                    // Enter without Shift sends message
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        this.sendMessage();
                    }
                    // Shift+Enter allows new line (default textarea behavior)
                },

                autoResize() {
                    const textarea = this.$refs.inputField;
                    if (textarea) {
                        textarea.style.height = 'auto';
                        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
                    }
                },

                formatMessage(content) {
                    if (!content) return '';
                    
                    let text = content
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    
                    text = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, 
                        '<a href="$2" class="text-violet-600 hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-300 underline font-medium" target="_self">$1</a>'
                    );
                    
                    text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
                    text = text.replace(/\*([^*]+)\*/g, '<em>$1</em>');
                    
                    return text;
                },

                // Prepare history for API (without actions, only role and content)
                getHistoryForApi() {
                    return this.messages.map(m => ({
                        role: m.role,
                        content: m.content
                    }));
                },

                async sendMessage() {
                    if (!this.inputMessage.trim() || this.isLoading) return;

                    const userMessage = this.inputMessage.trim();
                    this.inputMessage = '';
                    
                    // Reset textarea height
                    if (this.$refs.inputField) {
                        this.$refs.inputField.style.height = '42px';
                    }
                    
                    this.messages.push({
                        role: 'user',
                        content: userMessage
                    });

                    // Get history before adding user message for context
                    const historyForApi = this.getHistoryForApi().slice(0, -1); // Exclude the message we just added

                    this.scrollToBottom();
                    this.isLoading = true;

                    try {
                        const response = await this.$axios.post('{{ route("admin.ai_assistant.chat") }}', {
                            message: userMessage,
                            history: historyForApi
                        });

                        if (response.data.success) {
                            this.messages.push({
                                role: 'assistant',
                                content: response.data.response,
                                actions: response.data.actions
                            });
                        } else {
                            this.messages.push({
                                role: 'assistant',
                                content: response.data.response || 'Произошла ошибка при обработке запроса.'
                            });
                        }
                    } catch (error) {
                        console.error('AI Assistant Error:', error);
                        this.messages.push({
                            role: 'assistant',
                            content: 'Извините, произошла ошибка. Попробуйте позже.'
                        });
                    } finally {
                        this.isLoading = false;
                        this.scrollToBottom();
                    }
                },

                sendSuggestion(suggestion) {
                    this.inputMessage = suggestion;
                    this.sendMessage();
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        if (this.$refs.messagesContainer) {
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        }
                    });
                }
            }
        });
    </script>
@endPushOnce
