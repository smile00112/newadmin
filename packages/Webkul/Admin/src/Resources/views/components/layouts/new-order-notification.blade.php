<!-- New Order Notification Component -->
<v-new-order-notification></v-new-order-notification>

@pushOnce('scripts')
    <script type="text/x-template" id="v-new-order-notification-template">
        <div>
            <!-- Floating notification badge (always visible when there are pending orders) -->
            <div v-if="pendingCount > 0 && !showFullNotification"
                 @click="showFullNotification = true"
                 class="fixed bottom-6 right-6 z-[9999] cursor-pointer group">
                <div class="relative">
                    <!-- Pulsing background -->
                    <div class="absolute inset-0 rounded-2xl animate-ping opacity-75" style="background: linear-gradient(135deg, #f97316, #ef4444);"></div>
                    
                    <!-- Main badge -->
                    <div class="relative flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl hover:scale-105 transition-all duration-300" style="background: linear-gradient(135deg, #f97316, #ef4444); box-shadow: 0 20px 40px rgba(249, 115, 22, 0.4);">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl" style="background: rgba(255,255,255,0.25);">
                            <svg class="w-6 h-6 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-white">@{{ pendingCount }}</div>
                            <div class="text-xs text-white" style="opacity: 0.9;">@{{ pendingCount === 1 ? 'новый заказ' : 'новых заказов' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Full notification panel -->
            <transition name="slide-up">
                <div v-if="showFullNotification && pendingOrders.length > 0"
                     class="fixed bottom-6 right-6 z-[9999] w-96 max-h-[500px] rounded-2xl shadow-2xl overflow-hidden border border-orange-200">
                    
                    <!-- Header with gradient background -->
                    <div class="relative px-5 py-4" style="background: linear-gradient(135deg, #f97316, #ef4444, #ec4899);">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl" style="background: rgba(255,255,255,0.25);">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-bold text-base">🔔 Новые заказы!</h3>
                                    <p class="text-white text-xs" style="opacity: 0.85;">Требуют обработки</p>
                                </div>
                            </div>
                            <button @click="showFullNotification = false"
                                    class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors" style="background: rgba(255,255,255,0.2);">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Orders list -->
                    <div class="max-h-[320px] overflow-y-auto custom-scroll" style="background: linear-gradient(180deg, #fff7ed, #fef2f2);">
                        <div v-for="order in pendingOrders" :key="order.id"
                             class="group px-5 py-4 border-b cursor-pointer transition-colors hover:bg-white"
                             style="border-color: #fed7aa;"
                             @click="goToOrder(order.id)">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-gray-900 text-base">#@{{ order.increment_id }}</span>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full"
                                              :class="getStatusClass(order.status)">
                                            @{{ getStatusLabel(order.status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 truncate">
                                        @{{ order.customer_name || order.customer_email }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        @{{ formatTime(order.created_at) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-bold" style="color: #ea580c;">
                                        @{{ order.formatted_grand_total }}
                                    </div>
                                    <div class="text-xs text-gray-600">@{{ order.items_count }} товар(а)</div>
                                </div>
                            </div>
                            
                            <!-- Quick action -->
                            <div class="mt-3 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.stop="goToOrder(order.id)"
                                        class="flex-1 px-3 py-2 text-xs font-semibold text-white rounded-lg hover:shadow-lg transition-all"
                                        style="background: linear-gradient(135deg, #f97316, #ef4444);">
                                    🚀 Обработать заказ
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="px-5 py-3 border-t" style="background: linear-gradient(135deg, #ffedd5, #fee2e2); border-color: #fdba74;">
                        <a @click.prevent="goToAllPendingOrders"
                           href="#"
                           class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-semibold text-white rounded-xl hover:shadow-lg transition-all cursor-pointer"
                           style="background: linear-gradient(135deg, #f97316, #ef4444);">
                            <span>Все необработанные заказы</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </transition>
            
            <!-- Toast notification for new order -->
            <transition name="slide-down">
                <div v-if="newOrderToast"
                     class="fixed top-20 right-6 z-[9999] w-96">
                    <div class="relative overflow-hidden bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-green-200 dark:border-green-800">
                        <!-- Animated gradient border -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 via-emerald-500 to-teal-500 animate-gradient"></div>
                        
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl shadow-lg shadow-green-500/30 animate-bounce-slow">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">🎉 Новый заказ!</span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Заказ <span class="font-semibold">#@{{ newOrderToast.increment_id }}</span>
                                    </p>
                                    <p class="text-lg font-bold text-green-600 dark:text-green-400 mt-1">
                                        @{{ newOrderToast.formatted_grand_total }}
                                    </p>
                                </div>
                                <button @click="newOrderToast = null"
                                        class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="mt-4 flex gap-2">
                                <button @click="goToOrder(newOrderToast.id); newOrderToast = null;"
                                        class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl hover:shadow-lg hover:shadow-green-500/30 transition-all">
                                    Открыть заказ
                                </button>
                                <button @click="newOrderToast = null"
                                        class="px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                    Позже
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
            
            <!-- Audio element for notification sound -->
            <audio ref="notificationSound" preload="auto">
                <source src="data:audio/mpeg;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU4Ljc2LjEwMAAAAAAAAAAAAAAA//tQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAWGluZwAAAA8AAAACAAABhgC7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7//////////////////////////////////////////////////////////////////8AAAAATGF2YzU4LjEzAAAAAAAAAAAAAAAAJAAAAAAAAAAAAYYyAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/+0DEAAAPAAGkAAAAIAAANIAAAARMQU1FMy4xMDBVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/7QMQKA88AApQAAAAAAAA0gAAABExBTUUzLjEwMFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVU=" type="audio/mpeg">
            </audio>
        </div>
    </script>
    
    <script type="module">
        app.component('v-new-order-notification', {
            template: '#v-new-order-notification-template',
            
            data() {
                return {
                    pendingOrders: [],
                    pendingCount: 0,
                    showFullNotification: false,
                    newOrderToast: null,
                    lastCheckedOrderId: null,
                    checkInterval: null,
                    soundInterval: null,
                    audioContext: null,
                    isPlayingSound: false,
                    isFirstLoad: true,
                }
            },
            
            computed: {
                allPendingOrdersUrl() {
                    // Use the status filter card approach via sessionStorage
                    return '{{ route("admin.sales.orders.index") }}#pending';
                }
            },
            
            mounted() {
                this.checkNewOrders();
                // Check for new orders every 30 seconds
                this.checkInterval = setInterval(() => {
                    this.checkNewOrders();
                }, 30000);
                
                // Continuous sound loop - check every 3 seconds
                this.soundInterval = setInterval(() => {
                    this.manageContinuousSound();
                }, 3000);
                
                // Start sound after first check if there are orders
                setTimeout(() => {
                    this.manageContinuousSound();
                }, 2000);
                
                // Listen for sound toggle changes
                window.addEventListener('sound-alert-changed', (e) => {
                    this.manageContinuousSound();
                });
            },
            
            beforeUnmount() {
                if (this.checkInterval) {
                    clearInterval(this.checkInterval);
                }
                if (this.soundInterval) {
                    clearInterval(this.soundInterval);
                }
                this.stopSound();
            },
            
            methods: {
                async checkNewOrders() {
                    try {
                        const response = await fetch('{{ route("admin.api.orders.pending") }}');
                        const data = await response.json();
                        
                        if (data.success) {
                            const previousCount = this.pendingCount;
                            this.pendingOrders = data.orders;
                            this.pendingCount = data.count;
                            
                            // Manage continuous sound based on pending orders
                            this.manageContinuousSound();
                            
                            // Show toast for new orders (only after first load)
                            if (!this.isFirstLoad && data.count > previousCount && data.orders.length > 0) {
                                const newOrder = data.orders[0];
                                if (this.lastCheckedOrderId !== newOrder.id) {
                                    this.lastCheckedOrderId = newOrder.id;
                                    this.newOrderToast = newOrder;
                                    this.playNotificationSound();
                                    
                                    // Auto-hide toast after 10 seconds
                                    setTimeout(() => {
                                        if (this.newOrderToast && this.newOrderToast.id === newOrder.id) {
                                            this.newOrderToast = null;
                                        }
                                    }, 10000);
                                }
                            }
                            
                            this.isFirstLoad = false;
                        }
                    } catch (error) {
                        console.error('Error checking orders:', error);
                    }
                },
                
                playNotificationSound() {
                    // Sound module disabled — always return immediately
                    return;
                    const soundEnabled = localStorage.getItem('order_sound_alert') !== 'false';
                    if (!soundEnabled) return;
                    
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const playNote = (freq, start, dur) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.frequency.value = freq;
                            osc.type = 'sine';
                            gain.gain.setValueAtTime(0.3, start);
                            gain.gain.exponentialRampToValueAtTime(0.01, start + dur);
                            osc.start(start);
                            osc.stop(start + dur);
                        };
                        const now = ctx.currentTime;
                        playNote(523.25, now, 0.15);
                        playNote(659.25, now + 0.1, 0.15);
                        playNote(783.99, now + 0.2, 0.2);
                        playNote(1046.50, now + 0.3, 0.3);
                    } catch (e) {
                        console.log('Sound failed:', e);
                    }
                },
                
                manageContinuousSound() {
                    // Sound module disabled — stop any playing sound and return
                    this.stopSound();
                    return;
                    const soundEnabled = localStorage.getItem('order_sound_alert') !== 'false';
                    
                    if (soundEnabled && this.pendingCount > 0) {
                        // Start continuous beeping if not already playing
                        if (!this.isPlayingSound) {
                            this.startContinuousBeep();
                        }
                    } else {
                        // Stop sound if no pending orders or sound disabled
                        this.stopSound();
                    }
                },
                
                startContinuousBeep() {
                    if (this.isPlayingSound) return;
                    
                    try {
                        this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        this.isPlayingSound = true;
                        this.playBeepLoop();
                    } catch (e) {
                        console.log('Audio context failed:', e);
                    }
                },
                
                playBeepLoop() {
                    // Sound module disabled
                    this.stopSound();
                    return;
                    if (!this.isPlayingSound || !this.audioContext) return;
                    
                    const soundEnabled = localStorage.getItem('order_sound_alert') !== 'false';
                    if (!soundEnabled || this.pendingCount === 0) {
                        this.stopSound();
                        return;
                    }
                    
                    try {
                        const ctx = this.audioContext;
                        const now = ctx.currentTime;
                        
                        // Create beep pattern
                        const createBeep = (startTime, frequency, duration) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.frequency.value = frequency;
                            osc.type = 'sine';
                            gain.gain.setValueAtTime(0.2, startTime);
                            gain.gain.setValueAtTime(0.2, startTime + duration - 0.05);
                            gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
                            osc.start(startTime);
                            osc.stop(startTime + duration);
                        };
                        
                        // Two beeps pattern
                        createBeep(now, 880, 0.15);       // A5
                        createBeep(now + 0.2, 880, 0.15); // A5
                        
                        // Schedule next beep loop in 2 seconds
                        setTimeout(() => {
                            this.playBeepLoop();
                        }, 2000);
                        
                    } catch (e) {
                        console.log('Beep failed:', e);
                        this.isPlayingSound = false;
                    }
                },
                
                stopSound() {
                    this.isPlayingSound = false;
                    if (this.audioContext && this.audioContext.state !== 'closed') {
                        try {
                            this.audioContext.close();
                        } catch (e) {}
                    }
                    this.audioContext = null;
                },
                
                goToOrder(orderId) {
                    window.location.href = `{{ route('admin.sales.orders.index') }}/view/${orderId}`;
                },
                
                goToAllPendingOrders() {
                    // Save filter preference to localStorage so orders page can apply it
                    localStorage.setItem('orders_filter_status', 'pending');
                    window.location.href = '{{ route("admin.sales.orders.index") }}';
                },
                
                getStatusClass(status) {
                    const classes = {
                        'pending': 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                        'pending_payment': 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                        'processing': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                    };
                    return classes[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                },
                
                getStatusLabel(status) {
                    const labels = {
                        'pending': 'Новый',
                        'pending_payment': 'Ожидает оплаты',
                        'processing': 'В обработке',
                    };
                    return labels[status] || status;
                },
                
                formatTime(datetime) {
                    const date = new Date(datetime);
                    const now = new Date();
                    const diff = Math.floor((now - date) / 1000);
                    
                    if (diff < 60) return 'Только что';
                    if (diff < 3600) return `${Math.floor(diff / 60)} мин. назад`;
                    if (diff < 86400) return `${Math.floor(diff / 3600)} ч. назад`;
                    
                    return date.toLocaleDateString('ru-RU', { 
                        day: 'numeric', 
                        month: 'short',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }
        });
    </script>
    
    <style>
        .slide-up-enter-active,
        .slide-up-leave-active {
            transition: all 0.3s ease;
        }
        .slide-up-enter-from,
        .slide-up-leave-to {
            opacity: 0;
            transform: translateY(20px);
        }
        
        .slide-down-enter-active,
        .slide-down-leave-active {
            transition: all 0.3s ease;
        }
        .slide-down-enter-from,
        .slide-down-leave-to {
            opacity: 0;
            transform: translateY(-20px);
        }
        
        @keyframes gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient 2s ease infinite;
        }
        
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .animate-bounce-slow {
            animation: bounce-slow 2s ease-in-out infinite;
        }
        
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 2px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }
    </style>
    
    <!-- Sound Alert Toggle Component for Header -->
    <script type="text/x-template" id="v-sound-alert-toggle-template">
        <button 
            @click="toggleSound"
            class="group relative flex items-center justify-center rounded-xl p-2.5 transition-all duration-300 hover:shadow-sm active:scale-95"
            :class="soundEnabled 
                ? 'text-orange-500 hover:bg-gradient-to-br hover:from-orange-50 hover:to-amber-50 dark:hover:bg-gray-800/80' 
                : 'text-gray-400 hover:bg-gradient-to-br hover:from-gray-50 hover:to-gray-100 dark:text-gray-500 dark:hover:bg-gray-800/80'"
            :title="soundEnabled ? 'Звуковые оповещения включены' : 'Звуковые оповещения выключены'"
        >
            <!-- Sound On Icon -->
            <svg v-if="soundEnabled" class="w-5 h-5 sm:w-6 sm:h-6 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
            </svg>
            
            <!-- Sound Off Icon -->
            <svg v-else class="w-5 h-5 sm:w-6 sm:h-6 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
            </svg>
            
            <!-- Indicator dot when enabled -->
            <span v-if="soundEnabled" class="absolute top-1 right-1 w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
        </button>
    </script>
    
    <script type="module">
        app.component('v-sound-alert-toggle', {
            template: '#v-sound-alert-toggle-template',
            
            data() {
                return {
                    soundEnabled: localStorage.getItem('order_sound_alert') !== 'false'
                }
            },
            
            methods: {
                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    localStorage.setItem('order_sound_alert', this.soundEnabled ? 'true' : 'false');
                    
                    // Dispatch event to notify notification component
                    window.dispatchEvent(new CustomEvent('sound-alert-changed', { 
                        detail: { enabled: this.soundEnabled } 
                    }));
                }
            }
        });
    </script>
@endPushOnce
